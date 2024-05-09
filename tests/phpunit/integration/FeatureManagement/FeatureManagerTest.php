<?php

use MediaWiki\Context\RequestContext;
use MediaWiki\Skins\Vector\Constants;
use MediaWiki\Skins\Vector\FeatureManagement\FeatureManager;

/**
 * @coversDefaultClass \MediaWiki\Skins\Vector\FeatureManagement\FeatureManager
 */
class FeatureManagerTest extends \MediaWikiIntegrationTestCase {
	private function newFeatureManager(): FeatureManager {
		return new FeatureManager(
			$this->getServiceContainer()->getUserOptionsLookup(),
			RequestContext::getMain()
		);
	}

	public static function provideGetFeatureBodyClass() {
		return [
			[ CONSTANTS::FEATURE_LIMITED_WIDTH, false, 'vector-feature-limited-width-clientpref-0' ],
			[ CONSTANTS::FEATURE_LIMITED_WIDTH, true, 'vector-feature-limited-width-clientpref-1' ],
			[ CONSTANTS::FEATURE_TOC_PINNED, false, 'vector-feature-toc-pinned-clientpref-0' ],
			[ CONSTANTS::FEATURE_TOC_PINNED, true, 'vector-feature-toc-pinned-clientpref-1' ],
			[ 'default', false, 'vector-feature-default-disabled' ],
			[ 'default', true, 'vector-feature-default-enabled' ],
		];
	}

	/**
	 * @dataProvider provideGetFeatureBodyClass
	 * @covers ::getFeatureBodyClass basic usage
	 */
	public function testGetFeatureBodyClass( $feature, $enabled, $expected ) {
		$featureManager = $this->newFeatureManager();
		$featureManager->registerSimpleRequirement( 'requirement', $enabled );
		$featureManager->registerFeature( $feature, [ 'requirement' ] );
		// Title is required for checking whether or not the feature is excluded
		// based on page title.
		$context = RequestContext::getMain();
		$context->setTitle( Title::makeTitle( NS_MAIN, 'Main Page' ) );
		$this->assertSame( [ $expected ], $featureManager->getFeatureBodyClass() );
	}

	/**
	 * @covers ::getFeatureBodyClass returning multiple feature classes
	 */
	public function testGetFeatureBodyClassMultiple() {
		$featureManager = $this->newFeatureManager();
		$featureManager->registerSimpleRequirement( 'requirement', true );
		$featureManager->registerSimpleRequirement( 'disabled', false );
		$featureManager->registerFeature( 'sticky-header', [ 'requirement' ] );
		$featureManager->registerFeature( 'TableOfContents', [ 'requirement' ] );
		$featureManager->registerFeature( 'Test', [ 'disabled' ] );
		$this->assertEquals(
			[
				'vector-feature-sticky-header-enabled',
				'vector-feature-table-of-contents-enabled',
				'vector-feature-test-disabled'
			],
			$featureManager->getFeatureBodyClass()
		);
	}

	public static function provideGetFeatureBodyClassNightModeQueryParam() {
		return [
			[ 0, 'skin-theme-clientpref-day' ],
			[ 1, 'skin-theme-clientpref-night' ],
			[ 2, 'skin-theme-clientpref-os' ],
			[ 'day', 'skin-theme-clientpref-day' ],
			[ 'night', 'skin-theme-clientpref-night' ],
			[ 'os', 'skin-theme-clientpref-os' ],
			[ 'invalid', 'skin-theme-clientpref-day' ]
		];
	}

	/**
	 * @dataProvider provideGetFeatureBodyClassNightModeQueryParam
	 * @covers ::getFeatureBodyClass pref night mode specifics - disabled pages
	 */
	public function testGetFeatureBodyClassNightModeQueryParam( $value, $expected ) {
		$featureManager = $this->newFeatureManager();
		$featureManager->registerFeature( CONSTANTS::PREF_NIGHT_MODE, [] );

		$context = RequestContext::getMain();
		$context->setTitle( Title::makeTitle( NS_MAIN, 'test' ) );

		$request = $context->getRequest();
		$request->setVal( 'vectornightmode', $value );

		$this->assertEquals( [ $expected ], $featureManager->getFeatureBodyClass() );
	}

	/** ensure the class is present when disabled and absent when not */
	public static function provideGetFeatureBodyClassExcluded() {
		return [
			[ true ], [ false ]
		];
	}

	/**
	 * @dataProvider provideGetFeatureBodyClassExcluded
	 * @covers ::getFeatureBodyClass pref night mode specifics - disabled pages
	 */
	public function testGetFeatureBodyClassExcluded( $disabled ) {
		$featureManager = $this->newFeatureManager();
		$featureManager->registerFeature( CONSTANTS::PREF_NIGHT_MODE, [] );
		$featureManager->registerFeature( CONSTANTS::FEATURE_LIMITED_WIDTH, [] );

		$context = RequestContext::getMain();
		$context->setTitle( Title::makeTitle( NS_MAIN, 'Main Page' ) );

		$this->overrideConfigValues( [ 'VectorNightModeOptions' => [ 'exclude' => [ 'mainpage' => $disabled ] ] ] );
		$this->overrideConfigValues( [ 'VectorMaxWidthOptions' => [ 'exclude' => [ 'mainpage' => $disabled ] ] ] );

		$bodyClasses = $featureManager->getFeatureBodyClass();

		$this->assertEquals(
			in_array( 'skin-theme-clientpref--excluded', $bodyClasses ) &&
			in_array( 'vector-feature-limited-width-clientpref--excluded', $bodyClasses ),
			$disabled
		);
	}
}
