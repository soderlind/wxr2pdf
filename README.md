# WP-CLI add-on: wxr2pdf, convert an WordPress Export to PDF

## Syntax

`wp wxr-pdf convert [options]` 

## OPTIONS
- `file` **required**, path to WXR file for parsing.
- `--posttype=<posttype>` select post type. If not set, `post` is used. Separate post types using colon.
- `--language=<country_CODE>` loads languages/wxr2pdf_country_CODE.mo
- `--noimg` don't include images.

## EXAMPLES
```txt
  wp wxr-pdf convert file.wxr
  wp wxr-pdf convert file.wxr --language=nb_NO
  wp wxr-pdf convert file.wxr --noimg
  wp wxr-pdf convert file.wxr --posttype=page
  wp wxr-pdf convert file.wxr --nocomments
```
## Installation and activation
In `wp-content/plugins` do

1. `git clone https://github.com/soderlind/wxr2pdf`
1. `wp plugin activate wxr2pdf`

## Copyright and License

wxr2pdf is copyright 2017 Per Soderlind

wxr2pdf is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any later version.

wxr2pdf is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU Lesser General Public License along with the Extension. If not, see http://www.gnu.org/licenses/.
