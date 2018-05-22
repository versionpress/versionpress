import * as got from 'got';

// Based on https://stackoverflow.com/a/41830007/21728

(async () => {

  const repository = process.argv[2];
  const response = await got(`https://auth.docker.io/token?service=registry.docker.io&scope=repository:${repository}:pull`, {
    json: true
  });
  const token = response.body.token;

  const allTags = await got(`https://index.docker.io/v2/${repository}/tags/list`, {
    headers: {
      Authorization: `Bearer ${token}`
    },
    json: true,
  });

  for (let tag of allTags.body.tags) {
    let digest = await got.head(`https://index.docker.io/v2/${repository}/manifests/${tag}`, {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: `application/vnd.docker.distribution.manifest.v2+json`,
      },
    });

    console.log(`${tag} ${digest.headers['docker-content-digest']}`);
  }

})();
