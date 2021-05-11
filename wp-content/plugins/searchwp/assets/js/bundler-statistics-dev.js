const Bundler = require('parcel-bundler');
const Path = require('path');

const statisticsBundler = new Bundler(
	Path.join(__dirname, './src/statistics.js'),
	{
		outDir: Path.join(__dirname, './dist'),
		outFile: 'statistics.js',
		watch: true,
		cache: false,
		minify: false,
		hmr: false,
		sourceMaps: false
	}
);

statisticsBundler.bundle();
