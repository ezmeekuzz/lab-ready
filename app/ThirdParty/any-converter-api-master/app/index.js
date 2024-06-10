/**
 * Created by Stefan Hariton on 6/18/16.
 */

var index = {
  register: function(server, options, next) {
    server.register([
      {
        register: require('./endpoints/document')
      }
    ]);

    next();
  }
};

index.register.attributes = {
  name: 'Index',
  version: '0.0.1'
};

module.exports = index;