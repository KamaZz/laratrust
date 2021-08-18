## 6.1.0 (mayo 29, 2020)
  - Merge branch 'limit-groups-in-panel' into 6.x
  - Update docs
  - Update docs
  - Use a default model when entering the groups assignment view
  - Use display name when available in the panel
  - Build for production
  - Little improvements on the not removable groups and defaults for previously installed versions
  - Add show view for the not editable groups
  - Update docs
  - Add config file structure
  - Add possibility to avoid having groups removed from an user
  - Add the possibility to block  groups for edit and delete

## 6.0.2 (mayo 11, 2020)
  - Merge pull request #411 from siarheipashkevich/fix-config-typos
  - Fixed config typos
  - Update docs
  - Merge branch '6.x'
  - Fix broken links and update sitemap
  - Merge branch '6.x'
  - Add some screenshots to the docs
  - Merge branch '6.x'

## 6.0.1 (mayo 07, 2020)
  - Don't register the panel by default

## 6.0.0 (mayo 06, 2020)
- Add simple admin panel to manage groups, permissions and groups/permissions assignment to the users
- Change how the Seeder works, in order to only use the group structure we had before
- Remove the method `can` so we now support gates and policies out of the box
- Add `withoutGroup` and `withoutPermission` scopes
- Add support to receive multiple groups and permisions in the `whereGroupIs` and `wherePermissionIs` methods.
- Laratrust is now using semver.

