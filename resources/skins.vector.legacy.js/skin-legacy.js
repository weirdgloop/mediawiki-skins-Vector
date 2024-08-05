/* eslint-disable no-jquery/no-jquery-constructor */
/** @interface MediaWikiPageReadyModule */
const
	collapsibleTabs = require( './collapsibleTabs.js' ),
	portlets = require( './portlets.js' ),
	vector = require( './vector.js' ),
	initSearchLoader = require( './searchLoader.js' ).initSearchLoader,
	teleportTarget = /** @type {HTMLElement} */require( /** @type {string} */ ( 'mediawiki.page.ready' ) ).teleportTarget;

function main() {
	collapsibleTabs.init();
	$( vector.init );
	portlets.main();
	initSearchLoader( document );
	// pageReady.loadSearchModule( 'mediawiki.searchSuggest' );
	teleportTarget.classList.add( 'vector-body' );
}

main();
