# Isocontent

[![CI Status](https://github.com/un-zero-un/Isocontent/actions/workflows/ci.yml/badge.svg)](https://github.com/un-zero-un/Isocontent/actions/workflows/ci.yml)
[![Coverage Status](https://coveralls.io/repos/github/un-zero-un/Isocontent/badge.svg)](https://coveralls.io/github/un-zero-un/Isocontent)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fun-zero-un%2FIsocontent%2Fmain)](https://dashboard.stryker-mutator.io/reports/github.com/un-zero-un/Isocontent/main)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

**Isocontent** is a PHP library that transforms rich‑text content (such as HTML) into a platform‑agnostic Abstract Syntax Tree (AST). The AST can then be rendered back to any target format — HTML, JSON, React Native, mobile views, or anything else you need.

Typical use‑case: accept content from a WYSIWYG editor, store it as a portable AST, and render it on any platform.

## Installation

Requires PHP **>= 8.2**. The `DOMParser` needs `ext-dom` and `ext-libxml`; the `JSONRenderer` needs `ext-json`.

```bash
composer require unzeroun/isocontent
```

## End‑to‑End Demo

The `Isocontent` service is the main entry point. Register parsers and renderers, then parse and render content in any supported format:

```php
use Isocontent\Isocontent;
use Isocontent\Parser\DOMParser;
use Isocontent\Renderer\HTMLRenderer;
use Isocontent\Renderer\JSONRenderer;

$isocontent = new Isocontent(
    parsers:   [new DOMParser()],
    renderers: [new HTMLRenderer(), new JSONRenderer()],
);

// 1. Parse HTML into an AST
$ast = $isocontent->buildAST(
    '<h1>Hello</h1><p>This is <strong>rich</strong> content with a <a href="https://example.com">link</a>.</p>',
    'html',
);

// 2. Inspect the AST as a portable array (suitable for JSON storage / API responses)
$ast->toArray();
// [
//     [
//         'type'       => 'block',
//         'block_type' => 'title',
//         'arguments'  => ['level' => 1],
//         'children'   => [
//             ['type' => 'text', 'value' => 'Hello'],
//         ],
//     ],
//     [
//         'type'       => 'block',
//         'block_type' => 'paragraph',
//         'arguments'  => [],
//         'children'   => [
//             ['type' => 'text', 'value' => 'This is '],
//             [
//                 'type'       => 'block',
//                 'block_type' => 'strong',
//                 'arguments'  => [],
//                 'children'   => [
//                     ['type' => 'text', 'value' => 'rich'],
//                 ],
//             ],
//             ['type' => 'text', 'value' => ' content with a '],
//             [
//                 'type'       => 'block',
//                 'block_type' => 'link',
//                 'arguments'  => ['href' => 'https://example.com'],
//                 'children'   => [
//                     ['type' => 'text', 'value' => 'link'],
//                 ],
//             ],
//             ['type' => 'text', 'value' => '.'],
//         ],
//     ],
// ]

// 3. Render back to HTML
$isocontent->render($ast, 'html');
// '<h1>Hello</h1><p>This is <strong>rich</strong> content with a <a>link</a>.</p>'

// 4. Render to JSON
$isocontent->render($ast, 'json');
// The same array structure above, encoded as a JSON string
```

## Core Concepts

Isocontent models content as a tree of **nodes**:

| Class | Description |
|-------|-------------|
| `TextNode` | Leaf node holding a plain text value. |
| `BlockNode` | Structural element (paragraph, heading, link…) with optional children and typed arguments. |
| `NodeList` | Ordered collection of nodes — this is what parsers produce and renderers consume. |

### Block Types

The `DOMParser` maps HTML elements to the following block types:

| Block Type | HTML Tag(s) | Arguments |
|---|---|---|
| `paragraph` | `<p>` | — |
| `title` | `<h1>`–`<h6>` | `level` (int 1–6) |
| `strong` | `<strong>` | — |
| `emphasis` | `<em>` | — |
| `inline_text` | `<span>` | — |
| `link` | `<a>` | `href` (string) |
| `list` | `<ul>`, `<ol>` | `ordered` (bool) |
| `list_item` | `<li>` | — |
| `quote` | `<blockquote>` | — |
| `new_line` | `<br>` | — |
| `stripped` | `<del>` | — |
| `separator` | `<hr>` | — |
| `subscript` | `<sub>` | — |
| `superscript` | `<sup>` | — |
| `code` | `<code>` | — |
| `generic` | *any other element* | — |

## Parsers

A parser reads input in a given format and builds an AST through the `Builder`.

- **`DOMParser`** — parses HTML strings via PHP's `DOMDocument` (format: `html`)
- **`ArrayParser`** — re‑hydrates an AST from a PHP array or decoded JSON (format: `array`)

### Custom Parser

Implement the `Parser` interface:

```php
use Isocontent\AST\Builder;
use Isocontent\Parser\Parser;

final class MarkdownParser implements Parser
{
    public function supportsFormat(string $format): bool
    {
        return 'markdown' === $format;
    }

    public function parse(Builder $builder, mixed $input): void
    {
        // Build the AST using $builder->addTextNode() / $builder->addBlockNode()
    }
}
```

## Renderers

A renderer converts a `NodeList` into an output format.

- **`HTMLRenderer`** — renders to HTML (format: `html`). Tag mapping is [customizable](#custom-html-tag-mapping).
- **`JSONRenderer`** — renders to a JSON string (format: `json`).
- **`TextDebugRenderer`** — renders an indented tree for debugging (format: `text_debug`).

### Custom Renderer

Implement the `Renderer` interface:

```php
use Isocontent\AST\NodeList;
use Isocontent\Renderer\Renderer;

final class ReactNativeRenderer implements Renderer
{
    public function supportsFormat(string $format): bool
    {
        return 'react_native' === $format;
    }

    public function render(NodeList $ast): mixed
    {
        // Walk the AST and produce your output
    }
}
```

### Custom HTML Tag Mapping

The `HTMLRenderer` uses the [Specification pattern](https://en.wikipedia.org/wiki/Specification_pattern) to map block types to HTML tags. You can pass your own mapping to override the defaults:

```php
use Isocontent\Renderer\HTMLRenderer;
use Isocontent\Specs\BlockTypeMatch;
use Isocontent\Specs\BlockArgumentMatch;

$renderer = new HTMLRenderer([
    [(new BlockTypeMatch('title'))->and(new BlockArgumentMatch('level', 1)), 'h1'],
    [(new BlockTypeMatch('title'))->and(new BlockArgumentMatch('level', 2)), 'h2'],
    [new BlockTypeMatch('paragraph'), 'p'],
    [new BlockTypeMatch('strong'), 'b'],        // <b> instead of <strong>
    [new BlockTypeMatch('emphasis'), 'i'],       // <i> instead of <em>
    [new BlockTypeMatch('inline_text'), 'font'], // <font> instead of <span>
]);
```

Available specifications: `BlockTypeMatch` (match by type), `BlockArgumentMatch` (match by argument key/value), and `AllMatch` (compose with `->and()`).

## Symfony Integration

Register the bundle:

```php
// config/bundles.php
return [
    // ...
    Isocontent\Bridge\Symfony\Bundle\IsocontentBundle::class => ['all' => true],
];
```

The bundle auto‑discovers all `Parser` and `Renderer` implementations (tagged `isocontent.parser` / `isocontent.renderer`) and registers a public `Isocontent` service. All built‑in parsers and renderers are autowired out of the box.

```php
use Isocontent\Isocontent;

final class ContentController
{
    public function __construct(private readonly Isocontent $isocontent) {}

    public function show(): Response
    {
        $ast  = $this->isocontent->buildAST($html, 'html');
        $json = $this->isocontent->render($ast, 'json');
        // ...
    }
}
```

Two Symfony Form **data transformers** are also provided: `ASTToStringTransformer` (`Node|NodeList` ↔ rendered string) and `ASTToArrayTransformer` (`Node|NodeList` ↔ PHP array).

## Twig Integration

A Twig filter is available to render AST directly in templates:

```twig
{{ content|render_isocontent_ast }}
{{ content|render_isocontent_ast('json') }}
```

The filter accepts both `NodeList` objects and raw arrays.

## Testing

```bash
vendor/bin/phpunit          # Unit & E2E tests
vendor/bin/psalm            # Static analysis
vendor/bin/infection        # Mutation testing
vendor/bin/php-cs-fixer fix # Code style
```

CI runs on PHP 8.2, 8.3, 8.4, and 8.5 with both lowest and highest dependency versions.

## Contributing

Contributions are welcome! Fork the repository, create a feature branch, ensure all checks pass, and open a pull request.

## License

Isocontent is released under the [MIT License](https://opensource.org/licenses/MIT).
