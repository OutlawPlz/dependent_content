# Changelog

All notable changes to this project will be documented in this file.

## Unreleased

Log of unreleased changes.

### Added

- [#2](https://github.com/OutlawPlz/dependent_content/issues/2) - Added
contextual links.

## v0.2.1

Released on **2018/02/14**.

### Changed

- Improved `CHANGELOG.md` based on Keepachangelog.com site.

### Fixed

- Fixed delete bulk operation.
- [#1](https://github.com/OutlawPlz/dependent_content/issues/1) - Fixed empty
page on `/dependent-content/add` route.

## v0.2.0

Released on **2017/07/07**.

This release breaks compatibility with previous releases. Since
we're in early development, no upgrade path is provided.

### Added

- Makes entity translatable.
- Implemented revisions without UI.
- Added publishing actions.
- Added view filter published.
- Added `data_table` and `revision_data_table` to annotation.
- Added `DependentContentRevisionController`,
`DependentContentRevisionRevertForm`, `DependentContentRevisionDeleteForm`.
- Added revision related links and tasks.
- Added revision related view.
- Added `DependentContentTypeController`.

### Changed

- Use EntityChangedTrait and EntityPublishedTrait.
- Updated basic dependent content.
- Improved dependent content view.
- `DependentContentRevisionController` as a service.

### Fixed

- Fixed route path.
- Fixed edit path and local task.
- Fixed `revision_user` set to `NULL` on create.

### Removed

- Removed view filter status.

## v0.1.2

Released on **2017/06/23**.

### Added

- Added publish and unpublish bulk operation.
- Added delete bulk operation with confirmation form.
- Added `README.md` file.
- Added `basic` dependent content type.

### Changed

- Improved `dependent-content.html.twig` and preprocess function.

## v0.1.1

Released on **2017/06/23**.

### Fixed

- Fixed access dependent content permission bug.
- Fixed declaration incompatibility.

## v0.1.0

- First release.
