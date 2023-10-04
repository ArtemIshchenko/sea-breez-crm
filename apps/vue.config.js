module.exports = {
  runtimeCompiler: true,
  publicPath: '/admin/',
  devServer: {
    host: 'localhost',
    proxy: {
      '^/api': {
        target: 'http://localhost:8000',
        changeOrigin: true
      },
      '^/auth': {
        target: 'http://localhost:8000',
        changeOrigin: true
      },
      '^/uploads': {
        target: 'http://localhost:8000',
        changeOrigin: true
      }
    }
  }
}
