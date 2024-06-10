/**
 * Created by Stefan Hariton on 6/18/16.
 */

var Hapi = require('hapi');
var Good = require('good');

var server = new Hapi.Server();

const goodOptions = {
  ops: {
    interval: 1000
  },
  reporters: {
    console: [
      {
        module: 'good-squeeze',
        name: 'Squeeze',
        args: [{log: '*', response: '*'}]
      },
      {
        module: 'good-console'
      },
      'stdout'
    ]
  }
};

server.connection({
  port: '8080',
  routes: {
    cors: true
  }
});

server.route({
  method: ['GET', 'POST', 'PUT'],
  path: '/ping',
  handler: function(request, reply) {
    reply(request.headers);
  }
});

server.register([
  {
    register: Good,
    options: goodOptions
  },
  {
    register: require('./app/index')
  }],
  function (err) {
    if(err) {
      return console.error(err);
    }

    server.start(function() {
      console.log('Server running at: ', server.info.uri);
    })
  }
);