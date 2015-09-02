interface VersionPressConfig {

  api?: {
    root?: string;
    urlPrefix?: string;
    queryParam?: string;
    permalinkStructure?: boolean|string;
    nonce?: string;
  };

  routes?: {
    page?: string;
    home?: string;
    notFound?: string;
  };

}
