# Roadmap #

This page should give you an idea of where we are and what is planned for the future. Please be sure to understand our [rapid release cycle and versioning scheme](../release-notes).

<div class="note">
  <strong>Note: This list is tentative</strong>
  <p>The list below is tentative and subject to change any time but the **sort order roughly represents our priorities** – the higher up the list an item is, the higher chance it will be implemented sooner.</p>
</div>

* <del>Core versioning functionality.</del> *(Shipped as part of [1.0-alpha1](./1.0-alpha1).)*
* <del>Tracking of all the changes in a clean WordPress installation.</del> *(Partially shipped as part of [1.0-alpha1](./1.0-alpha1), notable updates in [1.0-alpha3](./1.0-alpha3).)*
* <del>Selective Undo.</del> *(Shipped as part of [1.0-alpha1](./1.0-alpha1).)*
* <del>Complete rollback to a previous state of a site.</del> *(Shipped as part of [1.0-alpha1](./1.0-alpha1).)*
* Remove dependency on `db.php`. *(Planned for 1.0 or 2.0, partly depends on [this WordPress issue](https://core.trac.wordpress.org/ticket/29710).)*
* Synchronization functionality incl. support for staging / testing environment. *(Planned for v2.0.)*
* Explicit support for selected 3rd party plugins (some 3rd party plugins work out of the box, some won't and for the most popular ones, we'll be adding explicit support). *(Planned to be implemented continually over time.)*
* UI improvements (side-by-side diffs, better display of change details etc.)
* Multisite support
* Localization
* Lowering system requirements, e.g., not requiring Git on the server or supporting PHP 5.2
