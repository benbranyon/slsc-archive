const Bundler = require('parcel-bundler');
const Path = require('path');

const settingsBundler = new Bundler(
	Path.join(__dirname, './src/settings.js'),
	{
		outDir: Path.join(__dirname, './dist'),
		outFile: 'settings.min.js',
		watch: true,
		cache: false,
		minify: true,
		hmr: false,
		sourceMaps: false
	}
);

settingsBundler.bundle();
