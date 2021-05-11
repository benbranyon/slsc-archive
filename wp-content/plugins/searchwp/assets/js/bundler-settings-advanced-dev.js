const Bundler = require('parcel-bundler');
const Path = require('path');

const settingsAdvancedBundler = new Bundler(
	Path.join(__dirname, './src/settings-advanced.js'),
	{
		outDir: Path.join(__dirname, './dist'),
		outFile: 'settings-advanced.js',
		watch: true,
		cache: false,
		minify: false,
		hmr: false,
		sourceMaps: false
	}
);

settingsAdvancedBundler.bundle();
