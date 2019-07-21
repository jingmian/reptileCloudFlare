<?php

require '../autoload.php';

$text = <<<'TEXT'
<!DOCTYPE html>
<html>
<head>
    <title>TEST</title>
</head>
<body>
    <div>
        <article>
            <title>Hello World, 你好世界！</title>
            <author>LiesAuer</author>
            <content>Hello World, 你好世界！Hello World, 你好世界！</content>
        </article>
    </div>
</body>
</html>
TEXT;

// DOC TYPE
var_dump(getMiddleText($text, '', 'html>', 0, $pos, INCLUDING_RIGHT));

// div with tag
var_dump(getMiddleText($text, '<div>', '</div>', 0, $pos, INCLUDING_BOTH), $pos);

// title without tag, starting from $pos
var_dump(getMiddleText($text, '<title>', '</title>', $pos, $pos));

// author
var_dump(getMiddleText($text, '<author>', '</author>', $pos, $pos));

// content
var_dump(getMiddleText($text, '<content>', '</content>', $pos, $pos));

// html
var_dump(getMiddleText($text, '<html>', '', 0, $pos, INCLUDING_LEFT));
