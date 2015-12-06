<?php
/**
 * Prints useful information about accesskeys
 */
require('header_req.php');
include('header.php');
?>
<h1>Index</h1>
<a href="#accesskey">Accesskey</a>
<a name="accesskey"></a>
<h2>Accesskey</h2>
Атрибут accesskey позволяет активировать ссылку с помощью некоторого
сочетания клавиш с заданной в коде ссылки буквой или цифрой.
Браузеры при этом используют различные комбинации клавиш.
Например, для accesskey="q" работают следующие сочетания:
<pre>
<i>Internet Explorer:</i> Alt + q
<i>Chrome:</i> Alt + q
<i>Opera:</i> Shift + Esc, q
<i>Safari:</i> Alt + S
<i>Firefox:</i> Shift + Alt + q
</pre>
<?php
include('footer.php');
?>
