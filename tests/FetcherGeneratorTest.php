<?php
declare(strict_types=1);

namespace flight\mcp\Tests;

use flight\mcp\Fetcher;
use PHPUnit\Framework\TestCase;

class FetcherGeneratorTest extends TestCase
{
    private Fetcher $fetcher;

    protected function setUp(): void
    {
        $this->fetcher = new Fetcher();
    }

    public function testGeneratePluginPageRequiredOnly(): void
    {
        $output = $this->fetcher->generatePluginPage('My Plugin', 'Does great things.');

        $this->assertStringContainsString('# My Plugin', $output);
        $this->assertStringContainsString('Does great things.', $output);
        $this->assertStringContainsString('## Installation', $output);
        $this->assertStringContainsString('Composer package name was not provided.', $output);
        $this->assertStringNotContainsString('Visit the [Github repository]', $output);
        $this->assertStringNotContainsString('## Setup in Flight', $output);
        $this->assertStringNotContainsString('## Usage', $output);
        $this->assertStringNotContainsString('## Configuration', $output);
        $this->assertStringNotContainsString('TODO', $output);
    }

    public function testGeneratePluginPageWithGithubUrl(): void
    {
        $output = $this->fetcher->generatePluginPage(
            'My Plugin',
            'Does great things.',
            github_url: 'https://github.com/example/my-plugin'
        );

        $this->assertStringContainsString(
            'Visit the [Github repository](https://github.com/example/my-plugin) for the full source code and details.',
            $output
        );
    }

    public function testGeneratePluginPageWithComposerPackage(): void
    {
        $output = $this->fetcher->generatePluginPage(
            'My Plugin',
            'Does great things.',
            composer_package: 'example/my-plugin'
        );

        $this->assertStringContainsString('composer require example/my-plugin', $output);
        $this->assertStringNotContainsString('Composer package name was not provided.', $output);
    }

    public function testGeneratePluginPageWithFlightSetupExample(): void
    {
        $setup = "Flight::register('myPlugin', MyPlugin::class);";
        $output = $this->fetcher->generatePluginPage('My Plugin', 'Desc.', flight_setup_example: $setup);

        $this->assertStringContainsString('## Setup in Flight', $output);
        $this->assertStringContainsString($setup, $output);
    }

    public function testGeneratePluginPageWithUsageExample(): void
    {
        $usage = "Flight::route('/test', function() { Flight::myPlugin()->doThing(); });";
        $output = $this->fetcher->generatePluginPage('My Plugin', 'Desc.', usage_example: $usage);

        $this->assertStringContainsString('## Usage', $output);
        $this->assertStringContainsString($usage, $output);
    }

    public function testGeneratePluginPageWithConfigOptions(): void
    {
        $output = $this->fetcher->generatePluginPage(
            'My Plugin',
            'Desc.',
            config_options: 'Set `timeout` to control the request timeout in seconds.'
        );

        $this->assertStringContainsString('## Configuration', $output);
        $this->assertStringContainsString('Set `timeout` to control the request timeout', $output);
    }

    public function testGeneratePluginPageConfigSectionOmittedWhenEmpty(): void
    {
        $output = $this->fetcher->generatePluginPage('My Plugin', 'Desc.');
        $this->assertStringNotContainsString('## Configuration', $output);
    }

    public function testGeneratePluginPageSeeAlsoLink(): void
    {
        $output = $this->fetcher->generatePluginPage(
            'My Plugin',
            'Desc.',
            see_also: 'SimplePdo | https://docs.flightphp.com/learn/simple-pdo'
        );

        $this->assertStringContainsString('## See Also', $output);
        $this->assertStringContainsString('[SimplePdo](https://docs.flightphp.com/learn/simple-pdo)', $output);
    }

    public function testGenerateLearnPageRequiredOnly(): void
    {
        $output = $this->fetcher->generateLearnPage('Routing', 'Flight has a powerful router.');

        $this->assertStringContainsString('# Routing', $output);
        $this->assertStringContainsString('## Overview', $output);
        $this->assertStringContainsString('Flight has a powerful router.', $output);
        $this->assertStringNotContainsString('## Basic Usage', $output);
        $this->assertStringNotContainsString('## Advanced Usage', $output);
        $this->assertStringNotContainsString('## Understanding', $output);
        $this->assertStringNotContainsString('## Key Points', $output);
        $this->assertStringNotContainsString('TODO', $output);
    }

    public function testGenerateLearnPageWithUnderstandingAndBasicExample(): void
    {
        $output = $this->fetcher->generateLearnPage(
            'Routing',
            'Intro.',
            understanding: 'Routes connect a URL to a callback.',
            basic_example: "Flight::route('/', function() { echo 'hello'; });"
        );

        $this->assertStringContainsString('## Understanding', $output);
        $this->assertStringContainsString('Routes connect a URL to a callback.', $output);
        $this->assertStringContainsString('## Basic Usage', $output);
        $this->assertStringContainsString("Flight::route('/', function()", $output);
    }

    public function testGenerateLearnPageAdvancedSectionOmittedWhenEmpty(): void
    {
        $output = $this->fetcher->generateLearnPage('Routing', 'Intro.');
        $this->assertStringNotContainsString('## Advanced Usage', $output);
    }

    public function testGenerateLearnPageWithAdvancedExample(): void
    {
        $output = $this->fetcher->generateLearnPage(
            'Routing',
            'Intro.',
            advanced_example: "Flight::route('GET /user/@id', function(\$id) { echo \$id; });"
        );

        $this->assertStringContainsString('## Advanced Usage', $output);
        $this->assertStringContainsString("Flight::route('GET /user/@id'", $output);
    }

    public function testGenerateLearnPageKeyPointsSectionOmittedWhenEmpty(): void
    {
        $output = $this->fetcher->generateLearnPage('Routing', 'Intro.');
        $this->assertStringNotContainsString('## Key Points', $output);
    }

    public function testGenerateLearnPageWithKeyPoints(): void
    {
        $output = $this->fetcher->generateLearnPage(
            'Routing',
            'Intro.',
            key_points: "Routes match top to bottom\nUse named parameters for dynamic segments"
        );

        $this->assertStringContainsString('## Key Points', $output);
        $this->assertStringContainsString('- Routes match top to bottom', $output);
        $this->assertStringContainsString('- Use named parameters for dynamic segments', $output);
    }

    public function testGenerateLearnPageKeyPointsSkipsBlankLines(): void
    {
        $output = $this->fetcher->generateLearnPage(
            'Routing',
            'Intro.',
            key_points: "First point\n\nSecond point"
        );

        $this->assertStringContainsString('- First point', $output);
        $this->assertStringContainsString('- Second point', $output);
        $this->assertStringNotContainsString("- \n", $output);
    }

    public function testGenerateLearnPageKeyPointsDoesNotDoubleDashPrefix(): void
    {
        $output = $this->fetcher->generateLearnPage(
            'Routing',
            'Intro.',
            key_points: "- Already has a dash"
        );

        $this->assertStringContainsString('- Already has a dash', $output);
        $this->assertStringNotContainsString('- - Already has a dash', $output);
    }

    public function testGenerateLearnPageChangelogAndTroubleshooting(): void
    {
        $output = $this->fetcher->generateLearnPage(
            'Routing',
            'Intro.',
            troubleshooting: "Route parameters match by order, not by name",
            changelog: 'v3 - Added resource routing'
        );

        $this->assertStringContainsString('## Troubleshooting', $output);
        $this->assertStringContainsString('- Route parameters match by order, not by name', $output);
        $this->assertStringContainsString('## Changelog', $output);
        $this->assertStringContainsString('- v3 - Added resource routing', $output);
    }

    public function testGenerateGuidePageRequiredOnly(): void
    {
        $output = $this->fetcher->generateGuidePage('Build a Blog', 'Learn to build a blog with Flight.');

        $this->assertStringContainsString('# Build a Blog', $output);
        $this->assertStringContainsString('Learn to build a blog with Flight.', $output);
        $this->assertStringContainsString('## Prerequisites', $output);
        $this->assertStringContainsString('- PHP 8.1+', $output);
        $this->assertStringContainsString('- Composer', $output);
        $this->assertStringNotContainsString('## Step 1:', $output);
        $this->assertStringNotContainsString('TODO', $output);
    }

    public function testGenerateGuidePageWithPrerequisites(): void
    {
        $output = $this->fetcher->generateGuidePage(
            'Guide',
            'Desc.',
            prerequisites: "PHP 8.2+\nComposer\nA database"
        );

        $this->assertStringContainsString('- PHP 8.2+', $output);
        $this->assertStringContainsString('- Composer', $output);
        $this->assertStringContainsString('- A database', $output);
        $this->assertStringNotContainsString('- PHP 8.1+', $output);
    }

    public function testGenerateGuidePagePrerequisitesDoesNotDoubleDashPrefix(): void
    {
        $output = $this->fetcher->generateGuidePage(
            'Guide',
            'Desc.',
            prerequisites: "- Already dashed"
        );

        $this->assertStringContainsString('- Already dashed', $output);
        $this->assertStringNotContainsString('- - Already dashed', $output);
    }

    public function testGenerateGuidePagePrerequisitesSkipsBlankLines(): void
    {
        $output = $this->fetcher->generateGuidePage(
            'Guide',
            'Desc.',
            prerequisites: "PHP 8.1+\n\nComposer"
        );

        $this->assertStringContainsString('- PHP 8.1+', $output);
        $this->assertStringContainsString('- Composer', $output);
        $this->assertStringNotContainsString("- \n", $output);
    }

    public function testGenerateGuidePageWithSteps(): void
    {
        $output = $this->fetcher->generateGuidePage(
            'Guide',
            'Desc.',
            steps: "Set Up the Project\nCreate Routes\nAdd Templates"
        );

        $this->assertStringContainsString('## Step 1: Set Up the Project', $output);
        $this->assertStringContainsString('## Step 2: Create Routes', $output);
        $this->assertStringContainsString('## Step 3: Add Templates', $output);
    }

    public function testGenerateGuidePageStepsWithBodies(): void
    {
        $output = $this->fetcher->generateGuidePage(
            'Guide',
            'Desc.',
            steps: "Set Up the Project\ncomposer create-project flightphp/skeleton my-app\n---\nAdd a Route\nEdit app/config/routes.php."
        );

        $this->assertStringContainsString("## Step 1: Set Up the Project\n\ncomposer create-project flightphp/skeleton my-app", $output);
        $this->assertStringContainsString("## Step 2: Add a Route\n\nEdit app/config/routes.php.", $output);
        $this->assertStringNotContainsString('TODO', $output);
    }

    public function testGenerateGuidePageStepsSkipsBlankLines(): void
    {
        $output = $this->fetcher->generateGuidePage(
            'Guide',
            'Desc.',
            steps: "First Step\n\nSecond Step"
        );

        $this->assertStringContainsString('## Step 1: First Step', $output);
        $this->assertStringContainsString('## Step 2: Second Step', $output);
        $this->assertStringNotContainsString('## Step 3:', $output);
    }

    public function testGenerateGuidePageStepsNumberedSequentially(): void
    {
        $output = $this->fetcher->generateGuidePage(
            'Guide',
            'Desc.',
            steps: "Alpha\nBeta\nGamma\nDelta"
        );

        $this->assertStringContainsString('## Step 1: Alpha', $output);
        $this->assertStringContainsString('## Step 2: Beta', $output);
        $this->assertStringContainsString('## Step 3: Gamma', $output);
        $this->assertStringContainsString('## Step 4: Delta', $output);
    }

    public function testGenerateGuidePageSeeAlso(): void
    {
        $output = $this->fetcher->generateGuidePage(
            'Guide',
            'Desc.',
            see_also: "- Already a bullet"
        );

        $this->assertStringContainsString('## See Also', $output);
        $this->assertStringContainsString('- Already a bullet', $output);
        $this->assertStringNotContainsString('- - Already a bullet', $output);
    }
}
