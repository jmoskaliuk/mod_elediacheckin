# User Documentation - mod_elediacheckin

## For Teachers

Add **eLeDia Check-In** as a course activity. Give it a name and choose which
kind of cards should appear. The default setup is suitable for simple check-in
and check-out use.

Teachers can configure:

- card goals
- categories
- target group
- context
- content language
- repeat behaviour
- previous-card button
- behaviour after all cards were shown
- own questions

Own questions are entered as one question per line. The activity can mix them
with the shared card pool or use only the teacher's own list.

## Learner Experience

Learners open the activity and see one card. Depending on the activity
configuration, they can move to another card, go back to a previous card or see
an empty-state message after the pool is exhausted.

The activity is designed for guided use in a course session. It does not store
learner answers or create grades.

## Presentation Use

Teachers can open the activity in presentation or popup mode for screen
sharing. The visual layout is intentionally focused on the card and action
buttons.

If the companion block is installed, a sidebar launcher can open the normal or
popup view directly from the course page.

## For Administrators

After installation, configure the content source under:

`Site administration -> Plugins -> Activity modules -> eLeDia Check-In`

Recommended first setup:

1. Keep the bundled source selected.
2. Save changes.
3. Run or wait for the content sync.
4. Check sync status and log messages.
5. Add a test activity in a course.

For Git-based content, use a raw `bundle.json` URL. A normal GitHub repository
HTML URL or `.git` clone URL is not sufficient because the sync service expects
JSON content.

## Companion Block

The companion block is optional. It requires this activity module and launches
an activity from the same course or frontpage context. Install it separately as
`block_elediacheckin`.

## Troubleshooting

- No cards appear: verify that content sync succeeded and that the selected
  filters match available cards.
- Git source fails: check that the configured URL returns raw JSON.
- A block launcher shows no options: create a Check-In activity in the same
  course or frontpage first.
- Language fallback looks unexpected: check activity language setting and admin
  fallback languages.

