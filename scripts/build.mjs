import * as esbuild from 'esbuild';
import { mkdir, readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';

const isWatch = process.argv.includes('--watch');

const buildOptions = {
  entryPoints: [
    'assets/src/admin/index.js',
    'assets/src/frontend/index.js',
  ],
  bundle: true,
  minify: !isWatch,
  sourcemap: isWatch,
  outdir: 'assets/dist',
  entryNames: '[dir]/index',
  target: ['es2020'],
  loader: {
    '.css': 'css',
  },
};

const fallbackAssets = [
  {
    name: 'admin',
    js: ['assets/src/admin/index.js'],
    css: ['assets/src/admin/index.css'],
  },
  {
    name: 'frontend',
    js: ['assets/src/frontend/index.js'],
    css: ['assets/src/frontend/index.css'],
  },
];

async function concatFiles(files) {
  const contents = await Promise.all(
    files.map((file) => readFile(file, 'utf8'))
  );

  return contents.join('\n\n');
}

async function runFallbackBuild() {
  for (const asset of fallbackAssets) {
    const outDir = path.join('assets', 'dist', asset.name);
    await mkdir(outDir, { recursive: true });
    await writeFile(
      path.join(outDir, 'index.js'),
      await concatFiles(asset.js),
      'utf8'
    );
    await writeFile(
      path.join(outDir, 'index.css'),
      await concatFiles(asset.css),
      'utf8'
    );
  }
}

try {
  if (isWatch) {
    const ctx = await esbuild.context(buildOptions);
    await ctx.watch();
    console.log('Watching for changes...');
  } else {
    await esbuild.build(buildOptions);
    console.log('Build complete.');
  }
} catch (error) {
  if (error && error.code === 'EPERM') {
    await runFallbackBuild();
    console.log('Build complete using fallback concatenation.');
  } else {
    throw error;
  }
}

