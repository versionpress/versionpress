///<reference path='../react/react.d.ts' />

declare module "react-router" {

  import React = require("react");

  //
  // Mixin
  // ----------------------------------------------------------------------
  interface Navigation {
    makePath(to: string, params?: {}, query?: {}): string;
    makeHref(to: string, params?: {}, query?: {}): string;
    transitionTo(to: string, params?: {}, query?: {}): void;
    replaceWith(to: string, params?: {}, query?: {}): void;
    goBack(): void;
  }

  interface RouteHandlerMixin {
    getRouteDepth(): number;
    createChildRouteHandler(props: {}): RouteHandler;
  }

  interface State {
    getPath(): string;
    getRoutes(): Route[];
    getPathname(): string;
    getParams(): {};
    getQuery(): {};
    isActive(to: string, params?: {}, query?: {}): boolean;
  }

  var Navigation: Navigation;
  var State: State;
  var RouteHandlerMixin: RouteHandlerMixin;


  //
  // Component
  // ----------------------------------------------------------------------
  // DefaultRoute
  interface DefaultRouteProp {
    name?: string;
    handler: React.ComponentClass<any>;
  }
  interface DefaultRoute extends React.ReactElement<DefaultRouteProp> {
    __react_router_default_route__: any; // dummy
  }
  interface DefaultRouteClass extends React.ComponentClass<DefaultRouteProp> {
    __react_router_default_route__: any; // dummy
  }

  // Link
  interface LinkProp {
    activeClassName?: string;
    to: string;
    params?: {};
    query?: {};
    onClick?: Function;
  }
  interface Link extends React.ReactElement<LinkProp>, Navigation, State {
    __react_router_link__: any; // dummy

    getHref(): string;
    getClassName(): string;
  }
  interface LinkClass extends React.ComponentClass<LinkProp> {
    __react_router_link__: any; // dummy
  }

  // NotFoundRoute
  interface NotFoundRouteProp {
    name?: string;
    handler: React.ComponentClass<any>;
  }
  interface NotFoundRoute extends React.ReactElement<NotFoundRouteProp> {
    __react_router_not_found_route__: any; // dummy
  }
  interface NotFoundRouteClass extends React.ComponentClass<NotFoundRouteProp> {
    __react_router_not_found_route__: any; // dummy
  }

  // Redirect
  interface RedirectProp {
    path?: string;
    from?: string;
    to?: string;
  }
  interface Redirect extends React.ReactElement<RedirectProp> {
    __react_router_redirect__: any; // dummy
  }
  interface RedirectClass extends React.ComponentClass<RedirectProp> {
    __react_router_redirect__: any; // dummy
  }

  // Route
  interface RouteProp {
    name?: string;
    path?: string;
    handler?: React.ComponentClass<any>;
    ignoreScrollBehavior?: boolean;
  }
  interface Route extends React.ReactElement<RouteProp> {
    __react_router_route__: any; // dummy
  }
  interface RouteClass extends React.ComponentClass<RouteProp> {
    __react_router_route__: any; // dummy
  }

  // RouteHandler
  interface RouteHandlerProp {}
  interface RouteHandler extends React.ReactElement<RouteHandlerProp>, RouteHandlerMixin {
    __react_router_route_handler__: any; // dummy
  }
  interface RouteHandlerClass extends React.ReactElement<RouteHandlerProp> {
    __react_router_route_handler__: any; // dummy
  }

  var DefaultRoute: DefaultRouteClass;
  var Link: LinkClass;
  var NotFoundRoute: NotFoundRouteClass;
  var Redirect: RedirectClass;
  var Route: RouteClass;
  var RouteHandler: RouteHandlerClass;


  //
  // Location
  // ----------------------------------------------------------------------
  interface LocationBase {
    push(path: string): void;
    replace(path: string): void;
    pop(): void;
    getCurrentPath(): void;
  }

  interface LocationListener {
    addChangeListener(listener: Function): void;
    removeChangeListener(listener: Function): void;
  }

  interface HashLocation extends LocationBase, LocationListener {}
  interface HistoryLocation extends LocationBase, LocationListener {}
  interface RefreshLocation extends LocationBase {}

  var HashLocation: HashLocation;
  var HistoryLocation: HistoryLocation;
  var RefreshLocation: RefreshLocation;


  //
  // Behavior
  // ----------------------------------------------------------------------
  interface ScrollBehaviorBase {
    updateScrollPosition(position: {x: number; y: number;}, actionType: string): void;
  }
  interface ImitateBrowserBehavior extends ScrollBehaviorBase {}
  interface ScrollToTopBehavior extends ScrollBehaviorBase {}

  var ImitateBrowserBehavior: ImitateBrowserBehavior;
  var ScrollToTopBehavior: ScrollToTopBehavior;


  //
  // Router
  // ----------------------------------------------------------------------
  interface Router extends React.ReactElement<any> {
    run(callback: RouterRunCallback): void;
  }

  interface RouterState {
    path: string;
    action: string;
    pathname: string;
    params: {};
    query: {};
    routes : Route[];
  }

  interface RouterCreateOption {
    routes: Route;
    location?: LocationBase;
    scrollBehavior?: ScrollBehaviorBase;
  }

  type RouterRunCallback = (Handler: RouteClass, state: RouterState) => void;

  function create(options: RouterCreateOption): Router;
  function run(routes: Route, callback: RouterRunCallback): Router;
  function run(routes: Route, location: LocationBase, callback: RouterRunCallback): Router;

  //
  // History
  // ----------------------------------------------------------------------
  interface History {
    back(): void;
    length: number;
  }
  var History: History;
}


declare module "react" {
  import ReactRouter = require("react-router");

  // for DefaultRoute
  function createElement(
    type: ReactRouter.DefaultRouteClass,
    props: ReactRouter.DefaultRouteProp,
    ...children: ReactNode[]): ReactRouter.DefaultRoute;

  // for Link
  function createElement(
    type: ReactRouter.LinkClass,
    props: ReactRouter.LinkProp,
    ...children: ReactNode[]): ReactRouter.Link;

  // for NotFoundRoute
  function createElement(
    type: ReactRouter.NotFoundRouteClass,
    props: ReactRouter.NotFoundRouteProp,
    ...children: ReactNode[]): ReactRouter.NotFoundRoute;

  // for Redirect
  function createElement(
    type: ReactRouter.RedirectClass,
    props: ReactRouter.RedirectProp,
    ...children: ReactNode[]): ReactRouter.Redirect;

  // for Route
  function createElement(
    type: ReactRouter.RouteClass,
    props: ReactRouter.RouteProp,
    ...children: ReactNode[]): ReactRouter.Route;

  // for RouteHandler
  function createElement(
    type: ReactRouter.RouteHandlerClass,
    props: ReactRouter.RouteHandlerProp,
    ...children: ReactNode[]): ReactRouter.RouteHandler;
}
