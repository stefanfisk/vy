<?php

use function StefanFisk\Vy\el;

el('div', [], 'foo');

?>
-----
<?php

use function StefanFisk\Vy\el;

el('div')('foo');

?>
