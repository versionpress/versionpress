import chalk from 'chalk';
import gql from 'graphql-tag';
import * as github from './utils/github';
import _ = require('lodash');
import * as execa from 'execa';
import * as arg from 'arg';
import matchAll = require('string.prototype.matchall');

const args = arg({
  '--help': Boolean,
  '-h': '--help',
});

if (args._.length === 0 || args['--help']) {
  console.log(`
  Lists pull requests, their related issues and other changes between two versions.

  Usage
    $ changelog <ref1..ref2>

  Examples
    $ changelog 4.0..master
    $ changelog 4cf80ca..ef39bb2

  Limitations
    - Maximum of 100 items are returned (hard limit).
`);
  process.exit();
}

interface GitRange {
  oldCommit: string;
  newCommit: string;
}

interface GitHubIssueOrPr {
  url: string;
  title: string;
  labels: string[];
}

interface PullRequest extends GitHubIssueOrPr {
  prNumber: string;
  relatedIssues: string[]; // e.g., ['#123', 'versionpress/internal#12']
}

interface Issue extends GitHubIssueOrPr {
  issueNumber: string;
}

interface Commit {
  sha1: string;
  commitMessage: string;
}

interface DiffBetweenVersions {
  pullRequests: PullRequest[];
  mergeCommitsWithoutPR: Commit[];
  directCommits: Commit[];
  noteworthyIssues: Issue[];
  noteworthyPrs: PullRequest[];
}

interface PrGqlResponse {
  repository: {
    [prname: string]: {
      title: string;
      url: string;
      body: string;
      labels: {
        nodes: { name: string }[];
      };
    };
  };
}

interface IssueGqlResponse {
  repository: {
    [issuename: string]: {
      title: string;
      url: string;
      body: string;
      labels: {
        nodes: { name: string }[];
      };
    };
  };
}

(async function main() {
  const range = parseRange(args._[0]);
  const { directCommits, pullRequests, mergeCommitsWithoutPR, noteworthyIssues, noteworthyPrs } = await getData(range);

  function printPullRequests(pullRequests: PullRequest[], sectionTitle: string) {
    if (pullRequests.length === 0) {
      return;
    }
    console.log('\n' + chalk.cyan(sectionTitle));
    pullRequests.forEach(pullRequest => {
      const { prNumber, title } = pullRequest;
      const relatedIssues = pullRequest.relatedIssues.join(', ');
      console.log(`#${prNumber} ${title}${relatedIssues.length > 0 ? ' (' + relatedIssues + ')' : ''}`);
    });
  }

  function printCommits(commits: Commit[], sectionTitle: string): void {
    if (commits.length === 0) {
      return;
    }
    console.log('\n' + chalk.cyan(sectionTitle));
    commits.forEach(commit => {
      const { sha1, commitMessage } = commit;
      console.log(`${sha1} ${commitMessage}`);
    });
  }

  if (noteworthyIssues.length > 0) {
    console.log(chalk.cyan('\nNOTEWORTHY ISSUES:'));
    noteworthyIssues.forEach(iss => {
      const { issueNumber, title } = iss;
      console.log(`#${issueNumber} ${title}`);
    });
  }

  printPullRequests(noteworthyPrs, 'NOTEWORTHY PULL REQUESTS:');
  printPullRequests(pullRequests, 'PULL REQUESTS:');

  printCommits(mergeCommitsWithoutPR, 'MERGE COMMITS WITHOUT PR:');
  printCommits(directCommits, 'DIRECT COMMITS TO MASTER:');
})();

async function getData(range: GitRange): Promise<DiffBetweenVersions> {
  github.exitIfNoGithubAccess();

  const result = await Promise.all([getMergeCommitsAndRelatedGithubIssues(range), getDirectCommits(range)]);
  return {
    pullRequests: result[0].pullRequests,
    mergeCommitsWithoutPR: result[0].mergeCommitsWithoutPR,
    noteworthyIssues: result[0].noteworthyIssues,
    noteworthyPrs: result[0].noteworthyPrs,
    directCommits: result[1],
  };
}

interface MergeCommitsAndRelatedGitHubData {
  pullRequests: PullRequest[];
  mergeCommitsWithoutPR: Commit[];
  noteworthyIssues: Issue[];
  noteworthyPrs: PullRequest[];
}

/**
 * Inspects merge commits and for those that look like PR merges fetches data from GitHub
 *
 * @param range
 */
async function getMergeCommitsAndRelatedGithubIssues(range: GitRange): Promise<MergeCommitsAndRelatedGitHubData> {
  const result = <MergeCommitsAndRelatedGitHubData>{
    pullRequests: [],
    mergeCommitsWithoutPR: [],
    noteworthyIssues: [],
    noteworthyPrs: [],
  };

  const mergeCommits = (await execa('git', getGitLogParams(range, '--merges'))).stdout;
  if (!mergeCommits) {
    return result;
  }

  const lines = mergeCommits.split(/\r?\n/);
  lines.forEach(line => {
    // https://regex101.com/r/gWjW78/5
    const matches = line.match(/([\S]+) ([^#\n]*#?(\d+)?.*)/)!;
    if (matches[3]) {
      result.pullRequests.push({ prNumber: matches[3], title: '', url: '', relatedIssues: [], labels: [] });
    } else {
      result.mergeCommitsWithoutPR.push({ sha1: matches[1], commitMessage: matches[2] });
    }
  });

  if (result.pullRequests.length === 0) {
    return result;
  }

  const query = gql`
    query {
        repository(owner: "versionpress", name: "versionpress") {
            ${result.pullRequests.map(pr => {
              return `
                pr${pr.prNumber}: pullRequest(number: ${pr.prNumber}) {
                    title,
                    url,
                    body
                    labels(first: 10) {
                        nodes {
                            name
                        }
                    }
                }
                `;
            })}
        }
    }
    `;

  const prQueryResponse = await github.query<PrGqlResponse>({ query });

  result.pullRequests.forEach(pr => {
    const prFromGithub = prQueryResponse.data.repository['pr' + pr.prNumber];
    pr.title = prFromGithub.title;
    pr.url = prFromGithub.url;
    pr.labels = _.map(prFromGithub.labels.nodes, _.property('name'));

    // https://regex101.com/r/YII6P2/2
    const matches = matchAll(
      prFromGithub.body,
      /(?:close|closes|fix|fixes|resolve|resolves|issue):? (?:\w[\w-.]+\/\w[\w-.]+|\B)(#[1-9]\d*)\b/gi
    );
    pr.relatedIssues = Array.from(matches).map(m => m[1]);
  });

  result.noteworthyPrs = _.filter(result.pullRequests, pr => pr.labels.includes('noteworthy'));

  // Now find noteworthy issues from pull requests and their related issues. We'll query GitHub and
  // find out which issues are labeled "noteworthy".

  const issueNumbers = _.uniq(
    _.compact(
      _.map(_.flattenDeep<string>(_.map(result.pullRequests, pr => pr.relatedIssues)), issue => {
        const match = issue.match(/^#(\d+)$/);
        return match ? parseInt(match[1], 10) : null;
      })
    )
  );

  if (issueNumbers.length === 0) {
    return result;
  }

  const issueQuery = gql`
    query {
        repository(owner: "versionpress", name: "versionpress") {
            ${issueNumbers.map(issue => {
              return `
                issue${issue}: issue(number: ${issue}) {
                    title,
                    url,
                    body,
                    labels(first: 10) {
                        nodes {
                            name
                        }
                    }
                }
                `;
            })}
        }
    }
    `;

  const issueQueryResponse = await github.query<IssueGqlResponse>({ query: issueQuery, errorPolicy: 'all' });

  result.noteworthyIssues = _.filter(
    _.compact(
      _.map(issueNumbers, issue => {
        const issueFromGithub = issueQueryResponse.data.repository['issue' + issue];
        if (!issueFromGithub) {
          return null;
        }
        return <Issue>{
          issueNumber: `${issue}`,
          title: issueFromGithub.title,
          url: issueFromGithub.url,
          labels: _.map(issueFromGithub.labels.nodes, _.property('name')),
        };
      })
    ),
    iss => iss.labels.includes('noteworthy')
  );

  return result;
}

async function getDirectCommits(range: GitRange): Promise<Commit[]> {
  const directCommitsFromGit = (await execa('git', getGitLogParams(range, '--no-merges'))).stdout;
  if (!directCommitsFromGit) {
    return [];
  }

  const lines = directCommitsFromGit.split(/\r?\n/);
  const directCommits = lines.map(line => {
    // https://regex101.com/r/gWjW78/1
    const matches = line.match(/(\S+) (.*)/)!;
    return <Commit>{ sha1: matches[1], commitMessage: matches[2] };
  });

  return directCommits;
}

function getGitLogParams(range: GitRange, mergeOption: '--merges' | '--no-merges') {
  return ['log', '--oneline', mergeOption, '--first-parent', '-100', `${range.oldCommit}..${range.newCommit}`];
}

export function parseRange(revisionRange: string): GitRange {
  // https://regex101.com/r/HFbS6B/1/
  if (!revisionRange.match(/.*[^\.]\.\.[^\.].*/)) {
    throw new Error('Please specify a range in the form oldCommit..newCommit');
  }
  const refs = revisionRange.split('..');
  return { oldCommit: refs[0], newCommit: refs[1] };
}
