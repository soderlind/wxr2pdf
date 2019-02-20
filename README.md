# Convert an WordPress Export to PDF

## OPTIONS
- `file` : Path to WXR file for parsing.
- `--posttype=<posttype>`  Select post type. If not set, "post" is used. Separate post types using colon.
- `--language=<country_CODE>` Loads languages/wxr2pdf_country_CODE.mo
- `--noimg` Don't include images.

## EXAMPLES
```txt
  wp wxr-pdf convert file.wxr
  wp wxr-pdf convert file.wxr --language=nb_NO
  wp wxr-pdf convert file.wxr --noimg
  wp wxr-pdf convert file.wxr --posttype=page
  wp wxr-pdf convert file.wxr --nocomments
```
