import * as dotenv from 'dotenv';
import { ApolloClient, HttpLink, InMemoryCache, WatchQueryOptions, ApolloQueryResult } from 'apollo-boost';
import nodeFetch from 'node-fetch';
import chalk from 'chalk';

dotenv.config({path: `${__dirname}/../.env`});

export function exitIfNoGithubAccess() {
    if (process.env.NODE_ENV === 'test') {
        return;
    }
    if (!process.env.GITHUB_TOKEN || process.env.GITHUB_TOKEN!.length < 10) {
        console.log(chalk.red('Please provide your GitHub personal access token in `.env`'));
        process.exit(1);
    }
}

const client = new ApolloClient({
    link: new HttpLink({
        uri: 'https://api.github.com/graphql',
        fetch: nodeFetch as any,
        headers: {
            authorization: `bearer ${process.env.GITHUB_TOKEN}`,
        },
    }),
    cache: new InMemoryCache(),
});

export async function query<T>(options: WatchQueryOptions): Promise<ApolloQueryResult<T>> {
    return client.query<T>(options);
}
