<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/services/EditorMarkupParser.php';

use App\Services\EditorMarkupParser;
use PHPUnit\Framework\TestCase;

final class EditorMarkupParserTest extends TestCase
{
  private EditorMarkupParser $parser;

  protected function setUp(): void
  {
    $this->parser = new EditorMarkupParser();
  }

  public function testParsesNormalParagraphWithBoldItalicAndCurlyReferences(): void
  {
    $input = "//Primer [debelny] <kurziv> {demo@redaktor.eu} {http://interslavic-language.org}\n//.";

    $html = $this->parser->parse($input);

    $this->assertStringContainsString('<p>Primer <strong>debelny</strong> <em>kurziv</em>', $html);
    $this->assertStringContainsString('<a href="mailto:demo@redaktor.eu">demo@redaktor.eu</a>', $html);
    $this->assertStringContainsString('<a href="http://interslavic-language.org" target="_blank" rel="noopener noreferrer">http://interslavic-language.org</a>', $html);
  }

  public function testParsesLineBreakAndEscapedSpecialCharacters(): void
  {
    $input = "//Prva\\\\druga \\/# \\/< \\/> \\/[ \\/] \\/{ \\/} \\//\n//.";

    $html = $this->parser->parse($input);

    $this->assertStringContainsString('<br>', $html);
    $this->assertStringContainsString('\\#', $html);
    $this->assertStringContainsString('\\&lt;', $html);
    $this->assertStringContainsString('\\&gt;', $html);
    $this->assertStringContainsString('\\[', $html);
    $this->assertStringContainsString('\\]', $html);
    $this->assertStringContainsString('\\{', $html);
    $this->assertStringContainsString('\\}', $html);
    $this->assertStringContainsString('\\/', $html);
    $this->assertStringNotContainsString('<em', $html);
  }

  public function testParsesBulletedAndNumberedParagraphsAsLists(): void
  {
    $input = "//*punkt 1\n//*punkt 2\n//#nomer 1\n//#nomer 2\n//.";

    $html = $this->parser->parse($input);

    $this->assertStringContainsString('<ul>', $html);
    $this->assertStringContainsString('<li>punkt 1</li>', $html);
    $this->assertStringContainsString('<li>punkt 2</li>', $html);
    $this->assertStringContainsString('</ul>', $html);

    $this->assertStringContainsString('<ol>', $html);
    $this->assertStringContainsString('<li>nomer 1</li>', $html);
    $this->assertStringContainsString('<li>nomer 2</li>', $html);
    $this->assertStringContainsString('</ol>', $html);
  }

  public function testParsesCenteredParagraphAndSmallHeading(): void
  {
    $input = "//=Centrovany tekst\n///malo nazvanje\n//.";

    $html = $this->parser->parse($input);

    $this->assertStringContainsString('<p class="text-center">Centrovany tekst</p>', $html);
    $this->assertMatchesRegularExpression('/<h2[^>]*>malo nazvanje<\/h2>/', $html);
    $this->assertStringContainsString('text-center', $html);
    $this->assertStringContainsString('small-heading', $html);
  }

  public function testParsesImageAndYoutubeEmbedBlocks(): void
  {
    $input = "//@veseli ljudi.jpg@Tuto jest popisanje\n//@https://www.youtube.com/embed/AaWWoiLQN84@Alla govori o svojem ucenju se\nmedzuslovjanskogo\n//.";

    $html = $this->parser->parse($input);

    $this->assertStringContainsString('<figure><img src="veseli ljudi.jpg" alt="Tuto jest popisanje">', $html);
    $this->assertStringContainsString('<figcaption>Tuto jest popisanje</figcaption></figure>', $html);

    $this->assertStringContainsString('<figure class="video-embed"><iframe src="https://www.youtube.com/embed/AaWWoiLQN84"', $html);
    $this->assertStringContainsString('<figcaption>Alla govori o svojem ucenju se medzuslovjanskogo</figcaption></figure>', $html);
  }

  public function testResolvesUnprefixedAttachmentAliasViaMediaSourceResolver(): void
  {
    $parser = new EditorMarkupParser(static function (string $source): ?string {
      if ($source === 'hero.jpg') {
        return '/posts/42/attachments/7';
      }

      return null;
    });

    $input = "//@hero.jpg@Nadpis\n//.";

    $html = $parser->parse($input);

    $this->assertStringContainsString('<img src="/posts/42/attachments/7" alt="Nadpis">', $html);
    $this->assertStringContainsString('<figcaption>Nadpis</figcaption>', $html);
  }

  public function testFallsBackToOriginalSourceWhenResolverReturnsEmptyString(): void
  {
    $parser = new EditorMarkupParser(static fn(string $source): string => '   ');

    $input = "//@hero.jpg@Nadpis\n//.";

    $html = $parser->parse($input);

    $this->assertStringContainsString('<img src="hero.jpg" alt="Nadpis">', $html);
  }

  public function testParsesExtendedEscapedSpecialCharacterForms(): void
  {
    $input = "//Esc \/# \/\\< \/\\> \/\\[ \/\\] \/\\{ \/\\} \// \/\\/\n//.";

    $html = $this->parser->parse($input);

    $this->assertStringContainsString('\\#', $html);
    $this->assertStringContainsString('\\&lt;', $html);
    $this->assertStringContainsString('\\&gt;', $html);
    $this->assertStringContainsString('\\[', $html);
    $this->assertStringContainsString('\\]', $html);
    $this->assertStringContainsString('\\{', $html);
    $this->assertStringContainsString('\\}', $html);
    $this->assertStringContainsString('\\/', $html);
  }

  public function testParsesSlashBackslashEscapedSpecialCharacterForms(): void
  {
    $input = "//Esc /\\# /\\< /\\> /\\[ /\\] /\\{ /\\} /\\/\n//.";

    $html = $this->parser->parse($input);

    $this->assertStringContainsString('#', $html);
    $this->assertStringContainsString('&lt;', $html);
    $this->assertStringContainsString('&gt;', $html);
    $this->assertStringContainsString('[', $html);
    $this->assertStringContainsString(']', $html);
    $this->assertStringContainsString('{', $html);
    $this->assertStringContainsString('}', $html);
    $this->assertStringContainsString('/', $html);
    $this->assertStringNotContainsString('\\', $html);
    $this->assertStringNotContainsString('<em', $html);
  }

  public function testParsesBackslashOnlyEscapedSpecialCharactersAndLineBreaks(): void
  {
    $input = "//Znaky \\<, \\>, \\[, \\], \\{, \\} trjeba pisati tako.\\\\Nova linija\n//.";

    $html = $this->parser->parse($input);

    $this->assertStringContainsString('Znaky &lt;, &gt;, [, ], {, } trjeba pisati tako.<br>Nova linija', $html);
    $this->assertStringNotContainsString('\\', $html);
    $this->assertStringNotContainsString('<em', $html);
  }

  public function testParsesDoubleBackslashEscapedSpecialCharactersWithoutExtraLineBreaks(): void
  {
    $input = "//Znaky \\\\<, \\\\>, \\\\[, \\\\], \\\\{, \\\\}.\n//.";

    $html = $this->parser->parse($input);

    $this->assertStringContainsString('Znaky &lt;, &gt;, [, ], {, }.', $html);
    $this->assertStringNotContainsString('<br>', $html);
    $this->assertStringNotContainsString('\\', $html);
  }

  public function testParsesRealReferenceSampleWithoutBreakingEscapedSymbolLine(): void
  {
    $input = <<<'MARKUP'
//Vsaka normalna odstava teksta počinaje se dvoma znakami /. [Debelny tekst trěba pisati do
uglatyh skobok]. <Kurzivny tekst trěba pisati do ostryh skobok>. Adresu e-maila pišemo do
kudravyh skobok tako: {demo@redaktor.eu}. Kogda hočemo link do www, potom takože do
kudravyh skobok: {http://interslavic-language.org} - potom avtomatično tvori se aktivny link.
\\Dvoma znakami \/ dělaje se nova linija teksta bez proměny stila odstavy.
\\Znak \# (plus simbol) trěba pisati \/# i znak \/ trěba pisati \//.
\\Znaky \<, \>, \[, \], \{, \} trěba pisati kako \/\<, \/\>, \/\[, \/\], \/\{, \/\}.
//*Punktovana odstava počinaje se takože dvoma znakami / i ješče znakom *.
//=Centrovana odstava teksta\\počinaje se takože\\dvoma znakami / i ješče znakom = .
//#Nomerovana odstava počinaje se takože dvoma znakami / i ješče znakom #.
//Malo nazvanje odstavy počinaje se trěmi znakami /. To ne jest nazvanje vsego teksta, ale toliko
kogda trěba někako podrobno nazvanje maloj časti teksta. Glavno nazvanje cělogo članka
generuje se avtomatično i ne jest častju koda.
///malo nazvanje
//Grafika pomočju znaka @ posle dvoh / i ješče znakom @ za imenem fajla, ktory može byti
//toliko v formatu JPG maksimalno velikom 1MB i trěba prigotoviti optimalno razlišenje i velikost.
//@veseli ljudi.jpg@Tuto jest popisanje grafiky
//Link do YouTube jest takože možny, ale toliko pomočju adresy "embed". Pozrite priměr:
//@https://www.youtube.com/embed/AaWWoiLQN84@Alla govori o svojem učenju se
medžuslovjanskogo
//Na koncu vsego teksta trěba napisati dva znaky /.
//.
MARKUP;

    $html = $this->parser->parse($input);

    $this->assertStringContainsString('Znaky &lt;, &gt;, [, ], {, } trěba pisati kako \\&lt;, \\&gt;, \\[, \\], \\{, \\}.', $html);
    $this->assertStringNotContainsString("kako <br>", $html);
    $this->assertStringNotContainsString('Dvoma znakami <br>', $html);
    $this->assertStringContainsString('<a href="mailto:demo@redaktor.eu">demo@redaktor.eu</a>', $html);
    $this->assertStringContainsString('<a href="http://interslavic-language.org" target="_blank" rel="noopener noreferrer">http://interslavic-language.org</a>', $html);
  }
}
