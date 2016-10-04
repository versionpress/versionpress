interface VersionPressConfig {

  api?: {
    root?: string;
    adminUrl?: string;
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
