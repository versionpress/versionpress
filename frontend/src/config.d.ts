interface VersionPressConfig {

  api: {
    root: string;
    urlPrefix: string;
    queryParam: string;
    prettyPermalinks: boolean;
  };

  routes: {
    page: string;
    home: string;
    notFound: string;
  };

}
