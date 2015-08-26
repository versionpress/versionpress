interface VersionPressConfig {

  api?: {
    root?: string;
    urlPrefix?: string;
    queryParam?: string;
    prettyPermalinks?: boolean;
    nonce?: string;
  };

  routes?: {
    page?: string;
    home?: string;
    notFound?: string;
  };

}
