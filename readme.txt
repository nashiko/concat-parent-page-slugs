=== Concat Parent Page Slugs ===
Contributors: nashiko
Donate link: https://www.amazon.co.jp/gp/registry/wishlist/33D4HIJ4X945K/
Tags: concat, parent, hierarchy, page, single, slug, template, load
Requires at least: 4.7
Tested up to: 4.8.2
Stable tag: 0.5.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add template file loading rule for 'Pages'.
When loading the template of the child page, preferentially loading the template file that concatenated parent page slugs.

== Description ==

The 'Concat Parent Page Slugs' add template file loading rule for 'Pages'.
When loading the template of the child page, preferentially loading the template file that concatenated parent page slugs.
For example, create a page with slug 'foo', 'bar'.
'foo' is the parent, 'bar' is the child.
Normally the template file for slug 'bar' will be 'page-bar.php', using this plugin adds parent slugs to child slug, like 'page-foo.bar.php'.
Of course it is also possible to change the delimiter to 'page-foo_bar.php'.


Sample.
[slug]        : [template]
company       : page-company.php
  |- info     : page-company.info.php
  |- access   : page-company.access.php
       |- map : page-company.access.map.php
     
product       : page-product.php
  |- info     : page-product.info.php


Concat Parent Page Slugs は、固定ページのテンプレートファイル読み込みルールを追加します。
子ページのテンプレートファイルを読み込む場合に、親ページのスラッグを連結したテンプレートファイルを優先的に読み込むようになります。
例えば、foo と bar というスラッグで固定ページを作成したと仮定します。 fooが親 で barが子 です。
通常であればスラッグ bar のテンプレートファイルは page-bar.php になりますが、このプラグインを使用すると親のスラッグが追加された page-foo.bar.php を読み込むようになります。
もちろん区切り文字を変更して page-foo_bar.php のようにすることも可能です。


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/concat-parent-page-slugs` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the `Settings->Concat Parent Page Slugs` screen to configure the plugin

== Screenshots ==

1. screenshot-1.png

== Changelog ==

= 0.5.1 =
* Delete unnecessary files.

= 0.5.0 =
* Initial release.

