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

    // --- generatePluginPage ---

    public function testGeneratePluginPageRequiredOnly(): void
    {
        $output = $this->fetcher->generatePluginPage('My Plugin', 'Does great things.');

        $this->assertStringContainsString('# My Plugin', $output);
        $this->assertStringContainsString('Does great things.', $output);
        $this->assertStringContainsString('## Installation', $output);
        $this->assertStringContainsString('# TODO: composer require your-package-here', $output);
        $this->assertStringContainsString('## Setup in Flight', $output);
        $this->assertStringContainsString('// TODO: Register the plugin with Flight here', $output);
        $this->assertStringContainsString('## Usage', $output);
        $this->assertStringContainsString('// TODO: Show how to use the plugin inside a Flight route', $output);
        $this->assertStringNotContainsString('Visit the [Github repository]', $output);
        $this->assertStringNotContainsString('## Configuration', $output);
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
        $this->assertStringNotContainsString('# TODO: composer require your-package-here', $output);
    }

    public function testGeneratePluginPageWithFlightSetupExample(): void
    {
        $setup = "Flight::register('myPlugin', MyPlugin::class);";
        $output = $this->fetcher->generatePluginPage('My Plugin', 'Desc.', flight_setup_example: $setup);

        $this->assertStringContainsString($setup, $output);
        $this->assertStringNotContainsString('// TODO: Register the plugin', $output);
    }

    public function testGeneratePluginPageWithUsageExample(): void
    {
        $usage = "Flight::route('/test', function() { Flight::myPlugin()->doThing(); });";
        $output = $this->fetcher->generatePluginPage('My Plugin', 'Desc.', usage_example: $usage);

        $this->assertStringContainsString($usage, $output);
        $this->assertStringNotContainsString('// TODO: Show how to use the plugin', $output);
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

    // --- generateLearnPage ---

    public function testGenerateLearnPageRequiredOnly(): void
    {
        $output = $this->fetcher->generateLearnPage('Routing', 'Flight has a powerful router.');

        $this->assertStringContainsString('# Routing', $output);
        $this->assertStringContainsString('Flight has a powerful router.', $output);
        $this->assertStringContainsString('## Basic Usage', $output);
        $this->assertStringContainsString('// TODO: Add a basic code example here', $output);
        $this->assertStringNotContainsString('## Advanced Usage', $output);
        $this->assertStringNotContainsString('## Key Points', $output);
    }

    public function testGenerateLearnPageWithBasicExample(): void
    {
        $output = $this->fetcher->generateLearnPage(
            'Routing',
            'Intro.',
            basic_example: "Flight::route('/', function() { echo 'hello'; });"
        );

        $this->assertStringContainsString("Flight::route('/', function()", $output);
        $this->assertStringNotContainsString('// TODO: Add a basic code example here', $output);
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

    // --- generateGuidePage ---

    public function testGenerateGuidePageRequiredOnly(): void
    {
        $output = $this->fetcher->generateGuidePage('Build a Blog', 'Learn to build a blog with Flight.');

        $this->assertStringContainsString('# Build a Blog', $output);
        $this->assertStringContainsString('Learn to build a blog with Flight.', $output);
        $this->assertStringContainsString('## Prerequisites', $output);
        $this->assertStringContainsString('- PHP 8.1+', $output);
        $this->assertStringContainsString('- Composer', $output);
        $this->assertStringContainsString('## Step 1: Getting Started', $output);
        $this->assertStringContainsString('## Step 2: Implementation', $output);
        $this->assertStringContainsString('## Step 3: Testing', $output);
        $this->assertStringContainsString('TODO: Fill in this step.', $output);
    }

    public function testGenerateGuidePageStepBodyIsPlainProse(): void
    {
        $output = $this->fetcher->generateGuidePage('Guide', 'Desc.');

        $this->assertStringNotContainsString('// TODO', $output);
        $this->assertStringContainsString('TODO: Fill in this step.', $output);
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
        $this->assertStringNotContainsString('## Step 1: Getting Started', $output);
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
}
