# wxr2pdf, convert an WordPress Export File to PDF

wxr2pdf is an WP-CLI extension that converts the content of an WordPress Export File to PDF.

## Syntax

`wp wxr2pdf [parameters]`

## Parameters

**Required**, either
- `file` path to the WordPress Export File.

or
- `--stdin`

Optional
- `--posttype=<posttype>` select post type. If not set, `post` is used. Separate post types using colon.
- `--language=<country_CODE>` loads languages/country_CODE.mo
- `--paper-format=<format>` default is `A4`, alternative is `Letter`
- `--paper-orientation=<oriantation>` default is `P` (portarit), alternative is `L` (landscape)
- `--watermark=<text>` add watermark to the PDF, enclose in quotes if more than one word.
- `--noimg` don't include images.
- `--nocomments` don't include comments

Add defaults options to your `wp-cli.yml` file, eksample:

```yml
wxr2pdf:
  paper-format: Letter
  watermark: "WXR2PDF 2019"
```

## Examples

[Export from WordPress](https://developer.wordpress.org/cli/commands/export/) using `wp export`

Convert to PDF, assuming the export is `wxr-file.xml` or reading from `STDIN`

```txt
  wp wxr2pdf wxr-file.xml
  wp export --stdout | wp wxr2pdf --stdin --posttype=page
  wp wxr2pdf wxr-file.xml --language=nb_NO
  wp wxr2pdf wxr-file.xml --noimg
  wp wxr2pdf wxr-file.xml --posttype=post:page
  wp wxr2pdf wxr-file.xml --nocomments
  wp wxr2pdf wxr-file.xml --paper-format=Letter --watermark="WP 2019"
```

The [example PDF](https://github.com/soderlind/wxr2pdf/raw/master/wxr2pdf-example.pdf) is created using `wp wxr2pdf --posttype=post --watermark=WXR2PDF wxr2pdf.WordPress.2019-02-26.xml`
- Site content from wptest.io

## Installation and activation

1. In `wp-content/plugins` do `git clone https://github.com/soderlind/wxr2pdf`
1. In `wp-content/plugins/wxr2pdf` run `composer install`
1. Activate the plugin: `wp plugin activate wxr2pdf`

## FAQ

1. **What's WXR?** The WordPress XML export file uses the WordPress Extended RSS format, also known as WXR.
1. **Why doesn't the PDF look like my site?** wxr2pdf converts only the content, not the design. It tries to remove embedded shortcodes.
1. **Why aren't any images included in the PDF?** The site and it's images must be online and accessible. Also, if images on your site are added using shortcodes, wxr2pdf might not be able to add them.

## Roadmap

- Continue refactoring code.
- ~~Support piping, e.g., let the user do `wp export --stdout | wp wxr2pdf`~~
- Convert to wp package

## Copyright and License

wxr2pdf is copyright 2019 Per Soderlind

wxr2pdf is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any later version.

wxr2pdf is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU Lesser General Public License along with the Extension. If not, see http://www.gnu.org/licenses/.
