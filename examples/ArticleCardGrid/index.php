<?php

declare(strict_types=1);

namespace StefanFisk\Vy\Examples\ArticleCardGrid;

use StefanFisk\Vy\Vy;

require __DIR__ . '/../../vendor/autoload.php';

$el = Layout::el(
    title: 'Vy Example',
)(
    CardGrid::el()(
        ArticleCard::el(
            articleId: 1,
        ),
        ArticleCard::el(
            articleId: 2,
        ),
        ArticleCard::el(
            articleId: 3,
        ),
    ),
);

$vy = new Vy();

echo $vy->render($el);
