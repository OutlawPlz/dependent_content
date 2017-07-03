# Changelog

All notable changes to this project will be documented in this file.

## v0.2.0

**Attention** - This release breaks compatibility with previous releases. Since 
we're in early development, no upgrade path is provided.

- Use EntityChangedTrait and EntityPublishedTrait. Translatable entity.

- Implemented revisions without UI. Added publishing actions. Fixed route path.

- Updated basic dependent content. Improved dependent content view. Fixed edit
path and local tasks. Removed view filter status. Added view filter published.

- Added data_table and revision_data_table to annotation. Fixed revision_user
null on create. Added DependentContentRevisionController, 
DependentContentRevisionRevertForm, DependentContentRevisionDeleteForm and the
relative links and tasks.

## v0.1.2

- Improved dependent-content.html.twig and preprocess function.

- Added bulk operations publish and unpublish.

- Added bulk operations delete with confirmation form.

- Added README.

- Added basic_dependent_content type.

## v0.1.1

- Fixed access dependent content permission bug. Fixed declaration 
incompatibility.

## v0.1.0

- First release.
 