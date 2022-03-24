<?php

declare(strict_types=1);

namespace Isocontent\Tests\Bridge\Twig\Extension;

use Composer\InstalledVersions;
use Isocontent\AST\NodeList;
use Isocontent\Bridge\Twig\Extension\IsocontentExtension;
use Isocontent\Isocontent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Twig\Node\Node;
use Twig\TwigFilter;

class IsocontentExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_renders_object_ast()
    {
        $nodeList   = $this->prophesize(NodeList::class);
        $isocontent = $this->prophesize(Isocontent::class);

        $isocontent->render($nodeList, 'html')->shouldBeCalled()->willReturn('value');

        $extension = new IsocontentExtension($isocontent->reveal());

        $this->assertSame('value', $extension->renderAST($nodeList->reveal()));
    }

    public function test_it_renders_array_ast()
    {
        $isocontent = $this->prophesize(Isocontent::class);
        $nodeList   = $this->prophesize(NodeList::class);

        $isocontent->render(Argument::type(NodeList::class), 'html')->shouldBeCalled()->willReturn('value');
        $isocontent->buildAST(Argument::type('array'), 'array')->shouldBeCalled()->willReturn($nodeList);

        $extension = new IsocontentExtension($isocontent->reveal());

        $this->assertSame('value', $extension->renderAST([['type' => 'text', 'value' => 'value']]));
    }

    public function test_it_declares_filters()
    {
        if (PHP_VERSION_ID >= 80100 && version_compare(InstalledVersions::getVersion('twig/twig'), '3.0.0') < 0) {
            $this->markTestSkipped('Twig 2 miss some return types');
        }

        $isocontent = $this->prophesize(Isocontent::class);
        $extension  = new IsocontentExtension($isocontent->reveal());

        $this->assertInstanceOf(TwigFilter::class, $extension->getFilters()[0]);
        $this->assertSame('render_isocontent_ast', $extension->getFilters()[0]->getName());
        $this->assertSame(['html'], $extension->getFilters()[0]->getSafe(new Node));
    }
}
