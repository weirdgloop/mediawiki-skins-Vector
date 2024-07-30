/**
 * @typedef {Record<string,string>} UrlParams
 * @param {string} title
 * @param {string} fulltext
 */

/**
 * @callback generateUrl
 * @param {RestResult|SearchResult|string} searchResult
 * @param {UrlParams} [params]
 * @param {string} [articlePath]
 * @return {string}
 */

/**
 * @typedef {Object} UrlGenerator
 * @property {generateUrl} generateUrl
 */

/**
 * Generates URLs for suggestions like those in MediaWiki's mediawiki.searchSuggest implementation.
 *
 * @param {MwMap} config
 * @return {UrlGenerator}
 */
function urlGenerator( config ) {
	// TODO: This is a placeholder for enabling customization of the URL generator.
	// wgVectorSearchUrlGenerator has not been defined as a config variable yet.
	return config.get( 'wgVectorSearchUrlGenerator', {
		/**
		 * @param {RestResult|SearchResult|string} suggestion
		 * @param {UrlParams} params
		 * @param {string} scriptPath
		 * @param {string} articlePath
		 * @return {string}
		 */
		generateUrl(
			suggestion,
			params = {},
			scriptPath = config.get( 'wgScript' ),
			articlePath = config.get( 'wgArticlePath' ),
		) {
			let page = '';
			if ( typeof suggestion !== 'string' ) {
				page = suggestion.title;
				if (suggestion.fragment) {
					page += `#${suggestion.fragment}`;
				}
			} else {
				page = suggestion;

				// Add `fulltext` query param to search within pages and for navigation
				// to the search results page (prevents being redirected to a certain
				// article).
				params = Object.assign( {}, params, {
					title: 'Special:Search',
					fulltext: '1',
					search: page
				} );

				const searchParams = new URLSearchParams(params);
				return `${scriptPath}?${searchParams.toString()}`;
			}

			return articlePath.replace('$1', page);
		}
	} );
}

/** @module urlGenerator */
module.exports = urlGenerator;
