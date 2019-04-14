import chalk from 'chalk';
import gql from 'graphql-tag';
import * as github from './utils/github';
import _ = require('lodash');
import * as execa from 'execa';
import * as arg from 'arg';
import matchAll = require('string.prototype.matchall');

const args = arg({
  '--help': Boolean,
  '--format': String,
  '-h': '--help',
});

if (args._.length === 0 || args['--help']) {
  console.log(`
  Lists pull requests, their related issues and other changes between two versions.

  Usage
    $ changelog [--format markdown] <ref1..ref2>

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
  const cliOutput = args['--format'] !== 'markdown';
  const range = parseRange(args._[0]);
  const { directCommits, pullRequests, mergeCommitsWithoutPR, noteworthyIssues, noteworthyPrs } = await getData(range);

  function printPullRequests(pullRequests: PullRequest[], sectionTitle: string) {
    if (pullRequests.length === 0) {
      return;
    }
    console.log(formatTitle(sectionTitle, cliOutput));
    pullRequests.forEach(pullRequest => {
      const { prNumber, title } = pullRequest;
      const relatedIssues = pullRequest.relatedIssues.join(', ');
      console.log(
        `${formatIssueOrPr(prNumber, 'pr', cliOutput)} ${title}${
          relatedIssues.length > 0 ? ' (' + formatRelatedIssues(relatedIssues, cliOutput) + ')' : ''
        }${formatEol(cliOutput)}`
      );
    });
  }

  function printCommits(commits: Commit[], sectionTitle: string): void {
    if (commits.length === 0) {
      return;
    }
    console.log(formatTitle(sectionTitle, cliOutput));
    commits.forEach(commit => {
      const { sha1, commitMessage } = commit;
      console.log(`${formatCommit(sha1, commitMessage, cliOutput)}${formatEol(cliOutput)}`);
    });
  }

  if (noteworthyIssues.length > 0) {
    console.log(formatTitle('Noteworthy issues', cliOutput));
    noteworthyIssues.forEach(iss => {
      const { issueNumber, title } = iss;
      console.log(`${formatIssueOrPr(issueNumber, 'issue', cliOutput)} ${title}${formatEol(cliOutput)}`);
    });
  }

  printPullRequests(noteworthyPrs, 'Noteworthy pull requests');
  printPullRequests(pullRequests, 'Pull requests');

  printCommits(mergeCommitsWithoutPR, 'Merge commits without a PR');
  printCommits(directCommits, 'Direct commits to master');
})();

function formatTitle(title: string, isCli: boolean) {
  return isCli ? `\n${chalk.cyan(title.toUpperCase())}` : `\n## ${title}\n`;
}

function formatCommit(sha1: string, commitMessage: string, isCli: boolean) {
  return isCli
    ? `${sha1} ${commitMessage}`
    : `[\`${sha1.substring(0, 9)}\`](https://github.com/versionpress/versionpress/commit/${sha1}) ${commitMessage}`;
}

function formatIssueOrPr(issueOrPrNumber: string, kind: 'pr' | 'issue', isCli: boolean) {
  return isCli
    ? `#${issueOrPrNumber}`
    : `[#${issueOrPrNumber}](https://github.com/versionpress/versionpress/${
        kind === 'issue' ? 'issues' : 'pull'
      }/${issueOrPrNumber})`;
}

function formatEol(isCli: boolean) {
  return isCli ? '' : `<br>`;
}

function formatRelatedIssues(str: string, isCli: boolean) {
  return isCli ? str : str.replace(/#(\d+)/g, '[#$1](https://github.com/versionpress/versionpress/issues/$1)');
}

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
