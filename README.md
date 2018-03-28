# wp-typography-groups
Plugin for grouping words (derived from WP Typography plugin)

## TODO
Configure the list of text fragments that should stay together 
(https://github.com/strarsis/wp-typography-groups/blob/master/class-typography-groups.php#L34).
E.g. 'New York' and 'Apple iPhone'.

## Details
The text fragments and individual words (delimited by space) are wrapped into `<span>` elements (`avoidwrap` class) which can be styled with `display: inline-block;`. This causes the browser to break these words only as last resort when available width becomes too scarce.
