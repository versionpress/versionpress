---
since: '3.0'
---

# Custom project structure

Some advanced users like having WordPress in its own directory or move plugins, themes or uploads in another directory. VersionPress supports some scenarios. Just remember that all files related to the site have to be under the project root ([`VP_PROJECT_ROOT`](../getting-started/configuration#vp_project_root)).

<div class="important">
  <p><strong>Note</strong></p>
  <p>It's highly recomended to adjust your project structure <strong>before</strong> activating VersionPress.</p>
</div>

## Giving WordPress its own directory

You can move WordPress into its own directory by following [instructions on Codex](https://codex.wordpress.org/Giving_WordPress_Its_Own_Directory). However, there is one extra step. You need to define `VP_PROJECT_ROOT` constant to let VersionPress know where it should create the repository. See the [configuration page](../getting-started/configuration#vp_project_root) for instructions.

<div class="note">
  <p><strong>Note</strong></p>
  <p>Be sure that the `.git` directory stays in the root directory if the project is already versioned.</p>
</div>

## Moving wp-content, plugin or uploads directories

It is possible to move these folders by following [instructions on Codex](https://codex.wordpress.org/Editing_wp-config.php#Moving_wp-content_folder). Be sure that you define constants referencing directories in the `wp-config.common.php` and constants containing URLs in the `wp-config.php` if VersionPress is already active.

## Moving VPDB directory

You can also rename or move the directory where VersionPress saves all its data. Use constant `VP_VPDB_DIR` to get it done. See the [configuration page](../getting-started/configuration#vp_vpdb_dir) for instructions.

<div class="note">
  <p><strong>Note</strong></p>
  <p>It will NOT be possible to undo changes before moving the directory.</p>
</div>
