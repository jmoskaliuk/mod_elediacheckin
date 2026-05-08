PHPCBF RESULT SUMMARY
--------------------------------------------------------------------------------
FILE                                                            FIXED  REMAINING
--------------------------------------------------------------------------------
/var/www/site/moodle/public/mod/elediacheckin/mod_form.php      54     2
/var/www/site/moodle/public/mod/elediacheckin/settings.php      15     0
...oodle/public/mod/elediacheckin/classes/privacy/provider.php  1      1
...e/moodle/public/mod/elediacheckin/classes/feature_flags.php  1      1
...blic/mod/elediacheckin/classes/content/schema_validator.php  3      4
...od/elediacheckin/classes/content/bundled_content_source.php  1      9
...elediacheckin/classes/content/bundle_signature_verifier.php  1      3
.../elediacheckin/classes/content/content_source_exception.php  1      2
.../elediacheckin/classes/content/content_source_interface.php  1      1
...public/mod/elediacheckin/classes/content/content_bundle.php  1      7
...ic/mod/elediacheckin/classes/content/git_content_source.php  1      16
...d/elediacheckin/classes/content/content_source_registry.php  1      1
...iacheckin/classes/content/eledia_premium_content_source.php  1      9
...e/public/mod/elediacheckin/classes/local/tour_installer.php  5      0
...od/elediacheckin/classes/local/admin/dashboard_renderer.php  45     6
...ic/mod/elediacheckin/classes/local/service/sync_service.php  6      2
.../mod/elediacheckin/classes/local/service/config_service.php  1      1
...c/mod/elediacheckin/classes/local/service/activity_pool.php  19     3
...d/elediacheckin/classes/local/service/category_provider.php  1      1
...c/mod/elediacheckin/classes/local/service/cache_service.php  1      1
...d/elediacheckin/classes/local/service/question_provider.php  1      3
...odle/public/mod/elediacheckin/classes/task/sync_content.php  1      1
...ic/mod/elediacheckin/classes/event/course_module_viewed.php  1      1
.../www/site/moodle/public/mod/elediacheckin/admin/actions.php  13     0
...www/site/moodle/public/mod/elediacheckin/admin/sync_log.php  3      0
/var/www/site/moodle/public/mod/elediacheckin/present.php       9      0
...e/moodle/public/mod/elediacheckin/lang/de/elediacheckin.php  16     107
...e/moodle/public/mod/elediacheckin/lang/en/elediacheckin.php  2      128
/var/www/site/moodle/public/mod/elediacheckin/view.php          9      3
/var/www/site/moodle/public/mod/elediacheckin/db/upgrade.php    195    1
...backup/moodle2/backup_elediacheckin_activity_task.class.php  1      0
...iacheckin/backup/moodle2/restore_elediacheckin_stepslib.php  1      1
...diacheckin/backup/moodle2/backup_elediacheckin_stepslib.php  1      1
...ackup/moodle2/restore_elediacheckin_activity_task.class.php  1      0
--------------------------------------------------------------------------------
A TOTAL OF 414 ERRORS WERE FIXED IN 34 FILES
--------------------------------------------------------------------------------

Time: 4.18 secs; Memory: 18MB


🎉 Done! Reload your Moodle page.
moskaliuk@Mac elediacheckin % docker compose -f ~/demo/compose.yml exec -T webserver \
  bash -c 'cd /var/www/site/moodle && vendor/bin/phpcs \
    --standard=moodle \
    --extensions=php \
    --ignore=*/vendor/*,*/node_modules/*,*/tests/* \
    public/mod/elediacheckin/'

FILE: /var/www/site/moodle/public/mod/elediacheckin/mod_form.php
--------------------------------------------------------------------------------
FOUND 54 ERRORS AND 2 WARNINGS AFFECTING 29 LINES
--------------------------------------------------------------------------------
  47 | ERROR   | [x] Opening brace must not be followed by a blank line
  88 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
  88 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
  89 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
  89 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
  92 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
  92 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 100 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 100 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 102 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 105 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 105 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 119 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 119 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 120 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 120 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 120 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 130 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 130 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 131 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 131 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 131 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 141 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 162 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 162 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 163 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 163 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 163 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 178 | WARNING | [ ] Inline comments must start with a capital letter, digit or
     |         |     3-dots sequence
 180 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 180 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 181 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 181 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 185 | WARNING | [ ] Inline comments must start with a capital letter, digit or
     |         |     3-dots sequence
 197 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 197 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 198 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 198 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 198 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 199 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 200 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 200 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 208 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 208 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 209 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 209 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 220 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 220 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 221 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 221 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 221 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 225 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 225 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 226 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 230 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 230 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 54 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: /var/www/site/moodle/public/mod/elediacheckin/settings.php
--------------------------------------------------------------------------------
FOUND 15 ERRORS AFFECTING 7 LINES
--------------------------------------------------------------------------------
  50 | ERROR | [x] Blank line found at start of control structure
 142 | ERROR | [x] Expected 1 space after comma in argument list; 3 found
 142 | ERROR | [x] Expected 1 space between the comma and
     |       |     "'mod_elediacheckin/contentsource'". Found: 3 spaces
 143 | ERROR | [x] Expected 1 space after comma in argument list; 3 found
 143 | ERROR | [x] Expected 1 space between the comma and
     |       |     "'mod_elediacheckin/contentsource'". Found: 3 spaces
 182 | ERROR | [x] Opening parenthesis of a multi-line function call must be
     |       |     the last content on the line
 183 | ERROR | [x] Multi-line function call not indented correctly; expected 8
     |       |     spaces but found 12
 183 | ERROR | [x] Only one argument is allowed per line in a multi-line
     |       |     function call
 183 | ERROR | [x] Only one argument is allowed per line in a multi-line
     |       |     function call
 183 | ERROR | [x] Closing parenthesis of a multi-line function call must be on
     |       |     a line by itself
 184 | ERROR | [x] Opening parenthesis of a multi-line function call must be
     |       |     the last content on the line
 185 | ERROR | [x] Multi-line function call not indented correctly; expected 8
     |       |     spaces but found 12
 185 | ERROR | [x] Only one argument is allowed per line in a multi-line
     |       |     function call
 185 | ERROR | [x] Only one argument is allowed per line in a multi-line
     |       |     function call
 185 | ERROR | [x] Closing parenthesis of a multi-line function call must be on
     |       |     a line by itself
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 15 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: /var/www/site/moodle/public/mod/elediacheckin/classes/privacy/provider.php
--------------------------------------------------------------------------------
FOUND 1 ERROR AND 1 WARNING AFFECTING 2 LINES
--------------------------------------------------------------------------------
 31 | WARNING | [ ] Unexpected MOODLE_INTERNAL check. No side effects or
    |         |     multiple artifacts detected.
 36 | ERROR   | [x] Opening brace must not be followed by a blank line
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 1 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: /var/www/site/moodle/public/mod/elediacheckin/classes/feature_flags.php
--------------------------------------------------------------------------------
FOUND 1 ERROR AND 1 WARNING AFFECTING 2 LINES
--------------------------------------------------------------------------------
 50 | WARNING | [ ] Unexpected MOODLE_INTERNAL check. No side effects or
    |         |     multiple artifacts detected.
 55 | ERROR   | [x] Opening brace must not be followed by a blank line
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 1 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: .../site/moodle/public/mod/elediacheckin/classes/content/schema_validator.php
--------------------------------------------------------------------------------
FOUND 3 ERRORS AND 1 WARNING AFFECTING 4 LINES
--------------------------------------------------------------------------------
  27 | WARNING | [ ] Unexpected MOODLE_INTERNAL check. No side effects or
     |         |     multiple artifacts detected.
  40 | ERROR   | [x] Opening brace must not be followed by a blank line
 216 | ERROR   | [x] The first expression of a multi-line control structure
     |         |     must be on the line after the opening parenthesis
 217 | ERROR   | [x] The closing parenthesis of a multi-line control structure
     |         |     must be on the line after the last expression
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 3 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: ...moodle/public/mod/elediacheckin/classes/content/bundled_content_source.php
--------------------------------------------------------------------------------
FOUND 1 ERROR AND 1 WARNING AFFECTING 2 LINES
--------------------------------------------------------------------------------
 27 | WARNING | [ ] Unexpected MOODLE_INTERNAL check. No side effects or
    |         |     multiple artifacts detected.
 36 | ERROR   | [x] Opening brace must not be followed by a blank line
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 1 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: ...dle/public/mod/elediacheckin/classes/content/bundle_signature_verifier.php
--------------------------------------------------------------------------------
FOUND 1 ERROR AND 2 WARNINGS AFFECTING 3 LINES
--------------------------------------------------------------------------------
 27 | WARNING | [ ] Unexpected MOODLE_INTERNAL check. No side effects or
    |         |     multiple artifacts detected.
 51 | ERROR   | [x] Opening brace must not be followed by a blank line
 81 | WARNING | [ ] Inline comments must start with a capital letter, digit or
    |         |     3-dots sequence
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 1 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: ...odle/public/mod/elediacheckin/classes/content/content_source_exception.php
--------------------------------------------------------------------------------
FOUND 1 ERROR AND 1 WARNING AFFECTING 2 LINES
--------------------------------------------------------------------------------
 27 | WARNING | [ ] Unexpected MOODLE_INTERNAL check. No side effects or
    |         |     multiple artifacts detected.
 35 | ERROR   | [x] Opening brace must not be followed by a blank line
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 1 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: ...odle/public/mod/elediacheckin/classes/content/content_source_interface.php
--------------------------------------------------------------------------------
FOUND 1 ERROR AND 1 WARNING AFFECTING 2 LINES
--------------------------------------------------------------------------------
 30 | WARNING | [ ] Unexpected MOODLE_INTERNAL check. No side effects or
    |         |     multiple artifacts detected.
 40 | ERROR   | [x] Opening brace must not be followed by a blank line
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 1 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: ...ww/site/moodle/public/mod/elediacheckin/classes/content/content_bundle.php
--------------------------------------------------------------------------------
FOUND 1 ERROR AND 1 WARNING AFFECTING 2 LINES
--------------------------------------------------------------------------------
 27 | WARNING | [ ] Unexpected MOODLE_INTERNAL check. No side effects or
    |         |     multiple artifacts detected.
 34 | ERROR   | [x] Opening brace must not be followed by a blank line
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 1 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: ...ite/moodle/public/mod/elediacheckin/classes/content/git_content_source.php
--------------------------------------------------------------------------------
FOUND 1 ERROR AND 7 WARNINGS AFFECTING 8 LINES
--------------------------------------------------------------------------------
  30 | WARNING | [ ] Unexpected MOODLE_INTERNAL check. No side effects or
     |         |     multiple artifacts detected.
  44 | ERROR   | [x] Opening brace must not be followed by a blank line
 168 | WARNING | [ ] The use of backticks in strings is not recommended
 170 | WARNING | [ ] The use of backticks in strings is not recommended
 171 | WARNING | [ ] The use of backticks in strings is not recommended
 178 | WARNING | [ ] The use of backticks in strings is not recommended
 181 | WARNING | [ ] The use of backticks in strings is not recommended
 186 | WARNING | [ ] The use of backticks in strings is not recommended
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 1 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: ...oodle/public/mod/elediacheckin/classes/content/content_source_registry.php
--------------------------------------------------------------------------------
FOUND 1 ERROR AND 1 WARNING AFFECTING 2 LINES
--------------------------------------------------------------------------------
 29 | WARNING | [ ] Unexpected MOODLE_INTERNAL check. No side effects or
    |         |     multiple artifacts detected.
 38 | ERROR   | [x] Opening brace must not be followed by a blank line
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 1 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: ...public/mod/elediacheckin/classes/content/eledia_premium_content_source.php
--------------------------------------------------------------------------------
FOUND 1 ERROR AND 2 WARNINGS AFFECTING 3 LINES
--------------------------------------------------------------------------------
  29 | WARNING | [ ] Unexpected MOODLE_INTERNAL check. No side effects or
     |         |     multiple artifacts detected.
  57 | ERROR   | [x] Opening brace must not be followed by a blank line
 167 | WARNING | [ ] Inline comments must end in full-stops, exclamation marks,
     |         |     or question marks
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 1 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: .../www/site/moodle/public/mod/elediacheckin/classes/local/tour_installer.php
--------------------------------------------------------------------------------
FOUND 5 ERRORS AFFECTING 5 LINES
--------------------------------------------------------------------------------
  31 | ERROR | [x] Opening brace must not be followed by a blank line
  53 | ERROR | [x] The first expression of a multi-line control structure must
     |       |     be on the line after the opening parenthesis
  54 | ERROR | [x] The closing parenthesis of a multi-line control structure
     |       |     must be on the line after the last expression
 125 | ERROR | [x] The first expression of a multi-line control structure must
     |       |     be on the line after the opening parenthesis
 126 | ERROR | [x] The closing parenthesis of a multi-line control structure
     |       |     must be on the line after the last expression
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 5 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: ...moodle/public/mod/elediacheckin/classes/local/admin/dashboard_renderer.php
--------------------------------------------------------------------------------
FOUND 45 ERRORS AND 3 WARNINGS AFFECTING 29 LINES
--------------------------------------------------------------------------------
  31 | ERROR   | [x] Opening brace must not be followed by a blank line
  54 | WARNING | [ ] Inline comments must start with a capital letter, digit or
     |         |     3-dots sequence
  67 | WARNING | [ ] Inline comments must start with a capital letter, digit or
     |         |     3-dots sequence
  85 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
  87 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
  87 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
  88 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
  89 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
  89 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
  92 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
  94 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
  94 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
  96 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
  98 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     12 spaces but found 16
  98 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 105 | WARNING | [ ] Inline comments must start with a capital letter, digit or
     |         |     3-dots sequence
 106 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 106 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 107 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 107 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 107 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 107 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 107 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 109 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 111 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 111 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 136 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 136 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 137 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     12 spaces but found 16
 137 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 141 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 143 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     20 spaces but found 24
 143 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 145 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 145 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 146 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     16 spaces but found 20
 146 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 155 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 156 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     16 spaces but found 20
 156 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 185 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 187 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 187 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 202 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 202 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 203 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 203 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 255 | ERROR   | [x] The closing brace for the class must go on the next line
     |         |     after the body
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 45 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: ...ite/moodle/public/mod/elediacheckin/classes/local/service/sync_service.php
--------------------------------------------------------------------------------
FOUND 6 ERRORS AND 1 WARNING AFFECTING 4 LINES
--------------------------------------------------------------------------------
  31 | WARNING | [ ] Unexpected MOODLE_INTERNAL check. No side effects or
     |         |     multiple artifacts detected.
  44 | ERROR   | [x] Opening brace must not be followed by a blank line
 161 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 161 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 161 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 162 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     12 spaces but found 16
 162 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 6 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: ...e/moodle/public/mod/elediacheckin/classes/local/service/config_service.php
--------------------------------------------------------------------------------
FOUND 1 ERROR AND 1 WARNING AFFECTING 2 LINES
--------------------------------------------------------------------------------
 27 | WARNING | [ ] Unexpected MOODLE_INTERNAL check. No side effects or
    |         |     multiple artifacts detected.
 32 | ERROR   | [x] Opening brace must not be followed by a blank line
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 1 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: ...te/moodle/public/mod/elediacheckin/classes/local/service/activity_pool.php
--------------------------------------------------------------------------------
FOUND 19 ERRORS AND 2 WARNINGS AFFECTING 13 LINES
--------------------------------------------------------------------------------
  35 | WARNING | [ ] Unexpected MOODLE_INTERNAL check. No side effects or
     |         |     multiple artifacts detected.
  40 | ERROR   | [x] Opening brace must not be followed by a blank line
 196 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 196 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 196 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 218 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 218 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 218 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 237 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 237 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 237 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 278 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 278 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 278 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 390 | WARNING | [ ] Inline comments must start with a capital letter, digit or
     |         |     3-dots sequence
 391 | ERROR   | [x] Expected 1 space before comment text but found 3; use
     |         |     block comment if you need indentation
 392 | ERROR   | [x] Expected 1 space before comment text but found 3; use
     |         |     block comment if you need indentation
 393 | ERROR   | [x] Expected 1 space before comment text but found 3; use
     |         |     block comment if you need indentation
 394 | ERROR   | [x] Expected 1 space before comment text but found 19; use
     |         |     block comment if you need indentation
 395 | ERROR   | [x] Expected 1 space before comment text but found 19; use
     |         |     block comment if you need indentation
 396 | ERROR   | [x] Expected 1 space before comment text but found 19; use
     |         |     block comment if you need indentation
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 19 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: ...oodle/public/mod/elediacheckin/classes/local/service/category_provider.php
--------------------------------------------------------------------------------
FOUND 1 ERROR AND 1 WARNING AFFECTING 2 LINES
--------------------------------------------------------------------------------
 27 | WARNING | [ ] Unexpected MOODLE_INTERNAL check. No side effects or
    |         |     multiple artifacts detected.
 32 | ERROR   | [x] Opening brace must not be followed by a blank line
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 1 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: ...te/moodle/public/mod/elediacheckin/classes/local/service/cache_service.php
--------------------------------------------------------------------------------
FOUND 1 ERROR AND 1 WARNING AFFECTING 2 LINES
--------------------------------------------------------------------------------
 27 | WARNING | [ ] Unexpected MOODLE_INTERNAL check. No side effects or
    |         |     multiple artifacts detected.
 32 | ERROR   | [x] Opening brace must not be followed by a blank line
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 1 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: ...oodle/public/mod/elediacheckin/classes/local/service/question_provider.php
--------------------------------------------------------------------------------
FOUND 1 ERROR AND 1 WARNING AFFECTING 2 LINES
--------------------------------------------------------------------------------
 31 | WARNING | [ ] Unexpected MOODLE_INTERNAL check. No side effects or
    |         |     multiple artifacts detected.
 39 | ERROR   | [x] Opening brace must not be followed by a blank line
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 1 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: ...var/www/site/moodle/public/mod/elediacheckin/classes/task/sync_content.php
--------------------------------------------------------------------------------
FOUND 1 ERROR AND 1 WARNING AFFECTING 2 LINES
--------------------------------------------------------------------------------
 29 | WARNING | [ ] Unexpected MOODLE_INTERNAL check. No side effects or
    |         |     multiple artifacts detected.
 34 | ERROR   | [x] Opening brace must not be followed by a blank line
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 1 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: ...ite/moodle/public/mod/elediacheckin/classes/event/course_module_viewed.php
--------------------------------------------------------------------------------
FOUND 1 ERROR AND 1 WARNING AFFECTING 2 LINES
--------------------------------------------------------------------------------
 27 | WARNING | [ ] Unexpected MOODLE_INTERNAL check. No side effects or
    |         |     multiple artifacts detected.
 32 | ERROR   | [x] Opening brace must not be followed by a blank line
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 1 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: /var/www/site/moodle/public/mod/elediacheckin/admin/actions.php
--------------------------------------------------------------------------------
FOUND 13 ERRORS AFFECTING 7 LINES
--------------------------------------------------------------------------------
 60 | ERROR | [x] Opening parenthesis of a multi-line function call must be the
    |       |     last content on the line
 65 | ERROR | [x] Multi-line function call not indented correctly; expected 12
    |       |     spaces but found 16
 65 | ERROR | [x] Closing parenthesis of a multi-line function call must be on
    |       |     a line by itself
 67 | ERROR | [x] Opening parenthesis of a multi-line function call must be the
    |       |     last content on the line
 68 | ERROR | [x] Multi-line function call not indented correctly; expected 12
    |       |     spaces but found 16
 68 | ERROR | [x] Only one argument is allowed per line in a multi-line
    |       |     function call
 68 | ERROR | [x] Closing parenthesis of a multi-line function call must be on
    |       |     a line by itself
 82 | ERROR | [x] Multi-line function call not indented correctly; expected 16
    |       |     spaces but found 20
 82 | ERROR | [x] Closing parenthesis of a multi-line function call must be on
    |       |     a line by itself
 87 | ERROR | [x] Multi-line function call not indented correctly; expected 16
    |       |     spaces but found 20
 87 | ERROR | [x] Closing parenthesis of a multi-line function call must be on
    |       |     a line by itself
 93 | ERROR | [x] Multi-line function call not indented correctly; expected 12
    |       |     spaces but found 16
 93 | ERROR | [x] Closing parenthesis of a multi-line function call must be on
    |       |     a line by itself
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 13 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: /var/www/site/moodle/public/mod/elediacheckin/admin/sync_log.php
--------------------------------------------------------------------------------
FOUND 3 ERRORS AFFECTING 2 LINES
--------------------------------------------------------------------------------
 37 | ERROR | [x] Opening parenthesis of a multi-line function call must be the
    |       |     last content on the line
 38 | ERROR | [x] Multi-line function call not indented correctly; expected 0
    |       |     spaces but found 4
 38 | ERROR | [x] Closing parenthesis of a multi-line function call must be on
    |       |     a line by itself
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 3 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: /var/www/site/moodle/public/mod/elediacheckin/present.php
--------------------------------------------------------------------------------
FOUND 9 ERRORS AFFECTING 3 LINES
--------------------------------------------------------------------------------
  78 | ERROR | [x] Only one argument is allowed per line in a multi-line
     |       |     function call
  78 | ERROR | [x] Only one argument is allowed per line in a multi-line
     |       |     function call
  78 | ERROR | [x] Only one argument is allowed per line in a multi-line
     |       |     function call
  78 | ERROR | [x] Only one argument is allowed per line in a multi-line
     |       |     function call
  78 | ERROR | [x] Only one argument is allowed per line in a multi-line
     |       |     function call
  78 | ERROR | [x] Only one argument is allowed per line in a multi-line
     |       |     function call
 139 | ERROR | [x] Opening parenthesis of a multi-line function call must be
     |       |     the last content on the line
 140 | ERROR | [x] Multi-line function call not indented correctly; expected 8
     |       |     spaces but found 27
 140 | ERROR | [x] Closing parenthesis of a multi-line function call must be on
     |       |     a line by itself
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 9 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: /var/www/site/moodle/public/mod/elediacheckin/lang/de/elediacheckin.php
--------------------------------------------------------------------------------
FOUND 0 ERRORS AND 8 WARNINGS AFFECTING 8 LINES
--------------------------------------------------------------------------------
  57 | WARNING | [x] The string key "cat_aha" is not in the correct order, it
     |         |     should be before "categories_help"
  59 | WARNING | [x] The string key "cat_aktion" is not in the correct order,
     |         |     it should be before "cat_alltag"
  93 | WARNING | [x] The string key "cat_werte" is not in the correct order, it
     |         |     should be before "cat_wertschaetzung"
 112 | WARNING | [x] The string key "contenterror_bundleinvalid" is not in the
     |         |     correct order, it should be before "contentlang_help"
 125 | WARNING | [x] The string key "contenterror_gitempty" is not in the
     |         |     correct order, it should be before "contenterror_githttp"
 140 | WARNING | [x] The string key "dashboard_runfailed" is not in the correct
     |         |     order, it should be before "dashboard_runnow"
 189 | WARNING | [ ] Unexpected comment found. Auto-fixing will not work after
     |         |     this comment
 191 | WARNING | [ ] The string key "noinstances" is not in the correct order,
     |         |     it should be before "noquestions"
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 6 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: /var/www/site/moodle/public/mod/elediacheckin/lang/en/elediacheckin.php
--------------------------------------------------------------------------------
FOUND 0 ERRORS AND 8 WARNINGS AFFECTING 8 LINES
--------------------------------------------------------------------------------
  58 | WARNING | [x] The string key "cat_aha" is not in the correct order, it
     |         |     should be before "categories_help"
  60 | WARNING | [x] The string key "cat_aktion" is not in the correct order,
     |         |     it should be before "cat_alltag"
  94 | WARNING | [x] The string key "cat_werte" is not in the correct order, it
     |         |     should be before "cat_wertschaetzung"
 113 | WARNING | [x] The string key "contenterror_bundleinvalid" is not in the
     |         |     correct order, it should be before "contentlang_help"
 126 | WARNING | [x] The string key "contenterror_gitempty" is not in the
     |         |     correct order, it should be before "contenterror_githttp"
 141 | WARNING | [x] The string key "dashboard_runfailed" is not in the correct
     |         |     order, it should be before "dashboard_runnow"
 190 | WARNING | [ ] Unexpected comment found. Auto-fixing will not work after
     |         |     this comment
 192 | WARNING | [ ] The string key "noinstances" is not in the correct order,
     |         |     it should be before "noquestions"
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 6 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: /var/www/site/moodle/public/mod/elediacheckin/lib.php
--------------------------------------------------------------------------------
FOUND 0 ERRORS AND 1 WARNING AFFECTING 1 LINE
--------------------------------------------------------------------------------
 25 | WARNING | Unexpected MOODLE_INTERNAL check. No side effects or multiple
    |         | artifacts detected.
--------------------------------------------------------------------------------


FILE: /var/www/site/moodle/public/mod/elediacheckin/view.php
--------------------------------------------------------------------------------
FOUND 9 ERRORS AND 3 WARNINGS AFFECTING 6 LINES
--------------------------------------------------------------------------------
  34 | WARNING | [ ] Inline comments must start with a capital letter, digit or
     |         |     3-dots sequence
 103 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 103 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 103 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 103 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 103 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 103 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 129 | WARNING | [ ] Inline comments must start with a capital letter, digit or
     |         |     3-dots sequence
 130 | WARNING | [ ] Inline comments must start with a capital letter, digit or
     |         |     3-dots sequence
 156 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 157 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 27
 157 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 9 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: /var/www/site/moodle/public/mod/elediacheckin/db/install.php
--------------------------------------------------------------------------------
FOUND 0 ERRORS AND 1 WARNING AFFECTING 1 LINE
--------------------------------------------------------------------------------
 30 | WARNING | Unexpected MOODLE_INTERNAL check. No side effects or multiple
    |         | artifacts detected.
--------------------------------------------------------------------------------


FILE: /var/www/site/moodle/public/mod/elediacheckin/db/upgrade.php
--------------------------------------------------------------------------------
FOUND 195 ERRORS AND 3 WARNINGS AFFECTING 60 LINES
--------------------------------------------------------------------------------
  25 | WARNING | [ ] Unexpected MOODLE_INTERNAL check. No side effects or
     |         |     multiple artifacts detected.
  40 | ERROR   | [x] Expected 1 space before comment text but found 2; use
     |         |     block comment if you need indentation
  41 | ERROR   | [x] Expected 1 space before comment text but found 4; use
     |         |     block comment if you need indentation
  42 | ERROR   | [x] Expected 1 space before comment text but found 4; use
     |         |     block comment if you need indentation
  43 | ERROR   | [x] Expected 1 space before comment text but found 4; use
     |         |     block comment if you need indentation
  44 | ERROR   | [x] Expected 1 space before comment text but found 2; use
     |         |     block comment if you need indentation
  45 | ERROR   | [x] Expected 1 space before comment text but found 4; use
     |         |     block comment if you need indentation
  46 | ERROR   | [x] Expected 1 space before comment text but found 2; use
     |         |     block comment if you need indentation
  47 | ERROR   | [x] Expected 1 space before comment text but found 4; use
     |         |     block comment if you need indentation
  48 | ERROR   | [x] Expected 1 space before comment text but found 4; use
     |         |     block comment if you need indentation
  49 | ERROR   | [x] Expected 1 space before comment text but found 2; use
     |         |     block comment if you need indentation
  54 | ERROR   | [x] Blank line found at start of control structure
  74 | ERROR   | [x] Expected 1 space after comma in argument list; 12 found
  74 | ERROR   | [x] Expected 1 space between the comma and
     |         |     "XMLDB_TYPE_INTEGER". Found: 12 spaces
  75 | ERROR   | [x] Expected 1 space after comma in argument list; 9 found
  75 | ERROR   | [x] Expected 1 space between the comma and
     |         |     "XMLDB_TYPE_INTEGER". Found: 9 spaces
  75 | ERROR   | [x] Expected 1 space after comma in argument list; 2 found
  75 | ERROR   | [x] Expected 1 space between the comma and "null". Found: 2
     |         |     spaces
  76 | ERROR   | [x] Expected 1 space after comma in argument list; 6 found
  76 | ERROR   | [x] Expected 1 space between the comma and "XMLDB_TYPE_CHAR".
     |         |     Found: 6 spaces
  76 | ERROR   | [x] Expected 1 space after comma in argument list; 4 found
  76 | ERROR   | [x] Expected 1 space between the comma and "'64'". Found: 4
     |         |     spaces
  77 | ERROR   | [x] Expected 1 space after comma in argument list; 4 found
  77 | ERROR   | [x] Expected 1 space between the comma and "'64'". Found: 4
     |         |     spaces
  78 | ERROR   | [x] Expected 1 space after comma in argument list; 4 found
  78 | ERROR   | [x] Expected 1 space between the comma and "XMLDB_TYPE_CHAR".
     |         |     Found: 4 spaces
  78 | ERROR   | [x] Expected 1 space after comma in argument list; 4 found
  78 | ERROR   | [x] Expected 1 space between the comma and "'128'". Found: 4
     |         |     spaces
  79 | ERROR   | [x] Expected 1 space after comma in argument list; 10 found
  79 | ERROR   | [x] Expected 1 space between the comma and "XMLDB_TYPE_CHAR".
     |         |     Found: 10 spaces
  79 | ERROR   | [x] Expected 1 space after comma in argument list; 4 found
  79 | ERROR   | [x] Expected 1 space between the comma and "'16'". Found: 4
     |         |     spaces
  80 | ERROR   | [x] Expected 1 space after comma in argument list; 4 found
  80 | ERROR   | [x] Expected 1 space between the comma and "XMLDB_TYPE_CHAR".
     |         |     Found: 4 spaces
  80 | ERROR   | [x] Expected 1 space after comma in argument list; 4 found
  80 | ERROR   | [x] Expected 1 space between the comma and "'255'". Found: 4
     |         |     spaces
  81 | ERROR   | [x] Expected 1 space after comma in argument list; 9 found
  81 | ERROR   | [x] Expected 1 space between the comma and "XMLDB_TYPE_TEXT".
     |         |     Found: 9 spaces
  81 | ERROR   | [x] Expected 1 space after comma in argument list; 4 found
  81 | ERROR   | [x] Expected 1 space between the comma and "null". Found: 4
     |         |     spaces
  82 | ERROR   | [x] Expected 1 space after comma in argument list; 5 found
  82 | ERROR   | [x] Expected 1 space between the comma and
     |         |     "XMLDB_TYPE_INTEGER". Found: 5 spaces
  82 | ERROR   | [x] Expected 1 space after comma in argument list; 2 found
  82 | ERROR   | [x] Expected 1 space between the comma and "null". Found: 2
     |         |     spaces
  83 | ERROR   | [x] Expected 1 space after comma in argument list; 7 found
  83 | ERROR   | [x] Expected 1 space between the comma and "XMLDB_TYPE_TEXT".
     |         |     Found: 7 spaces
  83 | ERROR   | [x] Expected 1 space after comma in argument list; 4 found
  83 | ERROR   | [x] Expected 1 space between the comma and "null". Found: 4
     |         |     spaces
  83 | ERROR   | [x] Expected 1 space after comma in argument list; 10 found
  83 | ERROR   | [x] Expected 1 space between the comma and "null". Found: 10
     |         |     spaces
  84 | ERROR   | [x] Expected 1 space after comma in argument list; 10 found
  84 | ERROR   | [x] Expected 1 space between the comma and "XMLDB_TYPE_CHAR".
     |         |     Found: 10 spaces
  84 | ERROR   | [x] Expected 1 space after comma in argument list; 4 found
  84 | ERROR   | [x] Expected 1 space between the comma and "'10'". Found: 4
     |         |     spaces
  85 | ERROR   | [x] Expected 1 space after comma in argument list; 8 found
  85 | ERROR   | [x] Expected 1 space between the comma and "XMLDB_TYPE_CHAR".
     |         |     Found: 8 spaces
  85 | ERROR   | [x] Expected 1 space after comma in argument list; 4 found
  85 | ERROR   | [x] Expected 1 space between the comma and "'255'". Found: 4
     |         |     spaces
  85 | ERROR   | [x] Expected 1 space after comma in argument list; 9 found
  85 | ERROR   | [x] Expected 1 space between the comma and "null". Found: 9
     |         |     spaces
  86 | ERROR   | [x] Expected 1 space after comma in argument list; 8 found
  86 | ERROR   | [x] Expected 1 space between the comma and "XMLDB_TYPE_CHAR".
     |         |     Found: 8 spaces
  86 | ERROR   | [x] Expected 1 space after comma in argument list; 4 found
  86 | ERROR   | [x] Expected 1 space between the comma and "'255'". Found: 4
     |         |     spaces
  86 | ERROR   | [x] Expected 1 space after comma in argument list; 9 found
  86 | ERROR   | [x] Expected 1 space between the comma and "null". Found: 9
     |         |     spaces
  87 | ERROR   | [x] Expected 1 space after comma in argument list; 7 found
  87 | ERROR   | [x] Expected 1 space between the comma and "XMLDB_TYPE_CHAR".
     |         |     Found: 7 spaces
  87 | ERROR   | [x] Expected 1 space after comma in argument list; 4 found
  87 | ERROR   | [x] Expected 1 space between the comma and "'64'". Found: 4
     |         |     spaces
  88 | ERROR   | [x] Expected 1 space after comma in argument list; 6 found
  88 | ERROR   | [x] Expected 1 space between the comma and "XMLDB_TYPE_CHAR".
     |         |     Found: 6 spaces
  88 | ERROR   | [x] Expected 1 space after comma in argument list; 4 found
  88 | ERROR   | [x] Expected 1 space between the comma and "'32'". Found: 4
     |         |     spaces
  89 | ERROR   | [x] Expected 1 space after comma in argument list; 7 found
  89 | ERROR   | [x] Expected 1 space between the comma and "XMLDB_TYPE_CHAR".
     |         |     Found: 7 spaces
  89 | ERROR   | [x] Expected 1 space after comma in argument list; 4 found
  89 | ERROR   | [x] Expected 1 space between the comma and "'16'". Found: 4
     |         |     spaces
  90 | ERROR   | [x] Expected 1 space after comma in argument list; 10 found
  90 | ERROR   | [x] Expected 1 space between the comma and "XMLDB_TYPE_CHAR".
     |         |     Found: 10 spaces
  90 | ERROR   | [x] Expected 1 space after comma in argument list; 4 found
  90 | ERROR   | [x] Expected 1 space between the comma and "'1333'". Found: 4
     |         |     spaces
  90 | ERROR   | [x] Expected 1 space after comma in argument list; 8 found
  90 | ERROR   | [x] Expected 1 space between the comma and "null". Found: 8
     |         |     spaces
  91 | ERROR   | [x] Expected 1 space after comma in argument list; 9 found
  91 | ERROR   | [x] Expected 1 space between the comma and "XMLDB_TYPE_CHAR".
     |         |     Found: 9 spaces
  91 | ERROR   | [x] Expected 1 space after comma in argument list; 4 found
  91 | ERROR   | [x] Expected 1 space between the comma and "'1333'". Found: 4
     |         |     spaces
  91 | ERROR   | [x] Expected 1 space after comma in argument list; 8 found
  91 | ERROR   | [x] Expected 1 space between the comma and "null". Found: 8
     |         |     spaces
  92 | ERROR   | [x] Expected 1 space after comma in argument list; 4 found
  92 | ERROR   | [x] Expected 1 space between the comma and
     |         |     "XMLDB_TYPE_INTEGER". Found: 4 spaces
  92 | ERROR   | [x] Expected 1 space after comma in argument list; 10 found
  92 | ERROR   | [x] Expected 1 space between the comma and "null". Found: 10
     |         |     spaces
  93 | ERROR   | [x] Expected 1 space after comma in argument list; 3 found
  93 | ERROR   | [x] Expected 1 space between the comma and
     |         |     "XMLDB_TYPE_INTEGER". Found: 3 spaces
  93 | ERROR   | [x] Expected 1 space after comma in argument list; 10 found
  93 | ERROR   | [x] Expected 1 space between the comma and "null". Found: 10
     |         |     spaces
  94 | ERROR   | [x] Expected 1 space after comma in argument list; 3 found
  94 | ERROR   | [x] Expected 1 space between the comma and
     |         |     "XMLDB_TYPE_INTEGER". Found: 3 spaces
  95 | ERROR   | [x] Expected 1 space after comma in argument list; 2 found
  95 | ERROR   | [x] Expected 1 space between the comma and
     |         |     "XMLDB_TYPE_INTEGER". Found: 2 spaces
 190 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 190 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 190 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 190 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 191 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 191 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 191 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 191 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 191 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 204 | ERROR   | [x] Expected 1 space before comment text but found 2; use
     |         |     block comment if you need indentation
 205 | ERROR   | [x] Expected 1 space before comment text but found 2; use
     |         |     block comment if you need indentation
 216 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 216 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 216 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 216 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 217 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 217 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 217 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 217 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 217 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 222 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 222 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 222 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 222 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 223 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 223 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 223 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 223 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 223 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 232 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 232 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 232 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 232 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 233 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 233 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 233 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 233 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 233 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 238 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 238 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 238 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 238 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 239 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 239 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 239 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 239 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 239 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 268 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 268 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 268 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 268 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 269 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 269 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 269 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 269 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 269 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 286 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 286 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 286 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 286 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 287 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 287 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 287 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 287 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 287 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 302 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 302 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 302 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 302 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 303 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 303 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 303 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 303 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 303 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 317 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 317 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 317 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 317 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 318 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 318 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 318 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 318 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 318 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 430 | ERROR   | [x] Opening parenthesis of a multi-line function call must be
     |         |     the last content on the line
 430 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 430 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 430 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 431 | ERROR   | [x] Multi-line function call not indented correctly; expected
     |         |     8 spaces but found 12
 431 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 431 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 431 | ERROR   | [x] Only one argument is allowed per line in a multi-line
     |         |     function call
 431 | ERROR   | [x] Closing parenthesis of a multi-line function call must be
     |         |     on a line by itself
 450 | ERROR   | [x] The first expression of a multi-line control structure
     |         |     must be on the line after the opening parenthesis
 451 | ERROR   | [x] The closing parenthesis of a multi-line control structure
     |         |     must be on the line after the last expression
 491 | WARNING | [ ] Inline comments must start with a capital letter, digit or
     |         |     3-dots sequence
 504 | WARNING | [ ] Inline comments must start with a capital letter, digit or
     |         |     3-dots sequence
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 195 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: .../elediacheckin/backup/moodle2/backup_elediacheckin_activity_task.class.php
--------------------------------------------------------------------------------
FOUND 1 ERROR AFFECTING 1 LINE
--------------------------------------------------------------------------------
 32 | ERROR | [x] Opening brace must not be followed by a blank line
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 1 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: ...public/mod/elediacheckin/backup/moodle2/restore_elediacheckin_stepslib.php
--------------------------------------------------------------------------------
FOUND 1 ERROR AND 1 WARNING AFFECTING 2 LINES
--------------------------------------------------------------------------------
 25 | WARNING | [ ] Unexpected MOODLE_INTERNAL check. No side effects or
    |         |     multiple artifacts detected.
 30 | ERROR   | [x] Opening brace must not be followed by a blank line
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 1 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: .../public/mod/elediacheckin/backup/moodle2/backup_elediacheckin_stepslib.php
--------------------------------------------------------------------------------
FOUND 1 ERROR AND 1 WARNING AFFECTING 2 LINES
--------------------------------------------------------------------------------
 28 | WARNING | [ ] Unexpected MOODLE_INTERNAL check. No side effects or
    |         |     multiple artifacts detected.
 33 | ERROR   | [x] Opening brace must not be followed by a blank line
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 1 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


FILE: ...elediacheckin/backup/moodle2/restore_elediacheckin_activity_task.class.php
--------------------------------------------------------------------------------
FOUND 1 ERROR AFFECTING 1 LINE
--------------------------------------------------------------------------------
 32 | ERROR | [x] Opening brace must not be followed by a blank line
--------------------------------------------------------------------------------
PHPCBF CAN FIX THE 1 MARKED SNIFF VIOLATIONS AUTOMATICALLY
--------------------------------------------------------------------------------


