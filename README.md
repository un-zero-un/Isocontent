# Isocontent

[![CI Status](https://github.com/un-zero-un/Isocontent/actions/workflows/ci.yml/badge.svg)](https://github.com/un-zero-un/Isocontent/actions/workflows/ci.yml)
[![Coverage Status](https://coveralls.io/repos/github/un-zero-un/Isocontent/badge.svg)](https://coveralls.io/github/un-zero-un/Isocontent)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fun-zero-un%2FIsocontent%2Fmain)](https://dashboard.stryker-mutator.io/reports/github.com/un-zero-un/Isocontent/main)
[![License](https://img.shields.io/github/license/un-zero-un/Isocontent)](https://github.com/un-zero-un/Isocontent/blob/main/LICENSE)

**Isocontent** is a PHP library that transforms rich‑text content (such as HTML) into a platform‑agnostic Abstract Syntax Tree (AST). The AST can then be rendered back to any target format — HTML, JSON, React Native, mobile views, or anything else you need.

Typical use‑case: accept content from a WYSIWYG editor, store it as a portable AST, and render it on any platform.

---

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Core Concepts](#core-concepts)
  - [AST Structure](#ast-structure)
  - [Block Types](#block-types)
- [Parsers](#parsers)
  - [DOMParser](#domparser)
  - [ArrayParser](#arrayparser)
  - [Custom Parser](#custom-parser)
- [Renderers](#renderers)
  - [HTMLRenderer](#htmlrenderer)
  - [JSONRenderer](#jsonrenderer)
  - [TextDebugRenderer](#textdebugrenderer)
  - [Custom Renderer](#custom-renderer)
- [Customizing the HTML Renderer](#customizing-the-html-renderer)
- [Symfony Integration](#symfony-integration)
  - [Bundle Setup](#bundle-setup)
  - [Using the Service](#using-the-service)
  - [Form Data Transformers](#form-data-transformers)
- [Twig Integration](#twig-integration)
- [AST Array Format](#ast-array-format)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

---

## Requirements

- PHP **>= 8.2**
- `ext-dom` and `ext-libxml` — required for the `DOMParser`
- `ext-json` — required for the `JSONRenderer`

## Installation

```bash
composer require unzeroun/isocontent
```

## Quick Start

```php
use Isocontent\Isocontent;
use Isocontent\Parser\DOMParser;
use Isocontent\Renderer\JSONRenderer;

$isocontent = new Isocontent(
    parsers:   [new DOMParser()],
    renderers: [new JSONRenderer()],
);

// Parse HTML into an AST
$ast = $isocontent->buildAST('<p>Hello <strong>world</strong></p>', 'html');

// Render the AST to JSON
echo $isocontent->render($ast, 'json');
```

---

## Core Concepts

### AST Structure

Isocontent models content as a tree of **nodes**:

| Class | Description |
|-------|-------------|
| `Node` | Interface implemented by every node. Defines `getType()` and `toArray()`. |
| `TextNode` | A leaf node holding a plain text value. |
| `BlockNode` | A node representing a structural element (paragraph, heading, link…). May contain children and typed arguments. |
| `NodeList` | An ordered collection of `Node` objects. This is what parsers produce and renderers consume. |

Nodes are created through static factory methods (`TextNode::fromText()`, `BlockNode::fromBlockType()`) or via the fluent `Builder`:

```php
use Isocontent\AST\Builder;

$builder = Builder::create();
$builder
    ->addBlockNode('paragraph')
        ->addTextNode('Hello ')
        ->addBlockNode('strong')
            ->addTextNode('world');

$ast = $builder->getAST(); // NodeList
```

### Block Types

The `DOMParser` maps HTML elements to the following block types:

| Block Type     | HTML Tag(s)         | Arguments         |
|----------------|---------------------|-------------------|
| `paragraph`    | `<p>`               | —                 |
| `title`        | `<h1>` – `<h6>`     | `level` (int 1–6) |
| `strong`       | `<strong>`          | —                 |
| `emphasis`     | `<em>`              | —                 |
| `inline_text`  | `<span>`            | —                 |
| `link`         | `<a>`               | `href` (string)   |
| `list`         | `<ul>`, `<ol>`      | `ordered` (bool)  |
| `list_item`    | `<li>`              | —                 |
| `quote`        | `<blockquote>`      | —                 |
| `new_line`     | `<br>`              | —                 |
| `stripped`     | `<del>`             | —                 |
| `separator`    | `<hr>`              | —                 |
| `subscript`    | `<sub>`             | —                 |
| `superscript`  | `<sup>`             | —                 |
| `code`         | `<code>`            | —                 |
| `generic`      | *any other element* | —                 |

---

## Parsers

A parser reads an input in a given format and builds an AST through the `Builder`.

### DOMParser

Parses an HTML string using PHP's `DOMDocument`. Supports format `html`.

```php
use Isocontent\Parser\DOMParser;

$parser = new DOMParser();
$parser->supportsFormat('html'); // true
```

### ArrayParser

Parses a PHP array that already follows the [AST array format](#ast-array-format). Supports format `array`.

```php
use Isocontent\Parser\ArrayParser;

$parser = new ArrayParser();
$parser->supportsFormat('array'); // true
```

This is useful for re‑hydrating an AST that was previously serialized to JSON or stored as an array.

### Custom Parser

Implement the `Isocontent\Parser\Parser` interface:

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

---

## Renderers

A renderer converts a `NodeList` AST into an output format.

### HTMLRenderer

Renders the AST back to an HTML string. Supports format `html`.

```php
use Isocontent\Renderer\HTMLRenderer;

$renderer = new HTMLRenderer();
echo $renderer->render($ast); // <p>Hello <strong>world</strong></p>
```

### JSONRenderer

Renders the AST to a JSON string. Supports format `json`.

```php
use Isocontent\Renderer\JSONRenderer;

$renderer = new JSONRenderer();
echo $renderer->render($ast);
```

### TextDebugRenderer

Renders the AST as an indented, human‑readable tree. Useful for debugging. Supports format `text_debug`.

### Custom Renderer

Implement the `Isocontent\Renderer\Renderer` interface:

```php
use Isocontent\AST\NodeList;
use Isocontent\Renderer\Renderer;

final class ReactNativeRenderer implements Renderer
{
    public function supportsFormat(string $format): bool
    {
        return $format === 'react_native';
    }

    public function render(NodeList $ast): mixed
    {
        // Walk the AST and produce your output
    }
}
```

---

## Customizing the HTML Renderer

The `HTMLRenderer` uses the [Specification pattern](https://en.wikipedia.org/wiki/Specification_pattern) to map AST block types to HTML tags. You can override the defaults by passing your own mapping:

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

Each entry is a pair of `[Specification, string]` where the specification is checked against every `BlockNode` and the string is the HTML tag name to use.

Available specifications:

| Class                | Description                                                                           |
|----------------------|---------------------------------------------------------------------------------------|
| `BlockTypeMatch`     | Matches a block by its type name.                                                     |
| `BlockArgumentMatch` | Matches a block by a specific argument key/value.                                     |
| `AllMatch`           | Composes multiple specifications (all must match). Built automatically via `->and()`. |

---

## Symfony Integration

### Bundle Setup

Register the bundle in your Symfony application:

```php
// config/bundles.php
return [
    // ...
    Isocontent\Bridge\Symfony\Bundle\IsocontentBundle::class => ['all' => true],
];
```

The bundle automatically:

- **Discovers** all classes implementing `Parser` and tags them with `isocontent.parser`.
- **Discovers** all classes implementing `Renderer` and tags them with `isocontent.renderer`.
- **Registers** the `Isocontent` service (public, also aliased as `isocontent`).

All built‑in parsers and renderers are autowired and autoconfigured out of the box.

### Using the Service

Inject the `Isocontent` service in your controllers or services:

```php
use Isocontent\Isocontent;

final class ContentController
{
    public function __construct(private readonly Isocontent $isocontent)
    {
    }

    public function show(): Response
    {
        $ast  = $this->isocontent->buildAST($html, 'html');
        $json = $this->isocontent->render($ast, 'json');

        // ...
    }
}
```

### Form Data Transformers

Two Symfony Form data transformers are provided:

| Transformer              | Direction                                      |
|--------------------------|------------------------------------------------|
| `ASTToStringTransformer` | `Node\|NodeList` ↔ rendered string (e.g. HTML) |
| `ASTToArrayTransformer`  | `Node\|NodeList` ↔ PHP array                   |

Use them when your form field stores AST data but needs to display or accept a different representation.

---

## Twig Integration

The library ships with a Twig extension that exposes the `render_isocontent_ast` filter:

```twig
{# Render a NodeList or an array to HTML (default format) #}
{{ content|render_isocontent_ast }}

{# Render to a specific format #}
{{ content|render_isocontent_ast('json') }}
```

The filter accepts both `NodeList` objects and raw arrays (which are parsed via the `ArrayParser` first).

---

## AST Array Format

Every node can be serialized to a plain array with `toArray()`. This is the canonical interchange format, suitable for JSON storage or API responses.

**TextNode:**

```json
{
    "type": "text",
    "value": "Hello world"
}
```

**BlockNode:**

```json
{
    "type": "block",
    "block_type": "paragraph",
    "arguments": {},
    "children": [
        { "type": "text", "value": "Hello " },
        {
            "type": "block",
            "block_type": "strong",
            "arguments": {},
            "children": [
                { "type": "text", "value": "world" }
            ]
        }
    ]
}
```

A `NodeList` serializes as an array of node objects. You can re-hydrate an AST from this format using the `ArrayParser`.
