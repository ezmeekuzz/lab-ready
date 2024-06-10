/**
 * Created by Stefan Hariton on 6/18/16.
 */

var saveFile = require('../common/index').save;
var unoconv = require('unoconv2');
var fs = require('fs');
var mmm = require('mmmagic');
var Magic = mmm.Magic;
var mime = require('mime-types');

var document = {
  register: function(server, options, next) {
    const magic = new Magic(mmm.MAGIC_MIME_TYPE);
    server.route({
      method: 'POST',
      path: '/document',
      config: {
        payload: {
          output: 'stream',
          parse: true,
          maxBytes: '1073741824'
        },
        handler: function (request, reply) {
          var path = saveFile(request.payload.file);
          var fileName = request.payload.file.hapi.filename;
          unoconv.convert(path, request.payload.to, function(err, result) {
            if (err) {
              console.log(err);
            }
            fs.unlinkSync(path);

            magic.detect(result, (err, data) => {
              return reply({
                type: data,
                file: result,
                fileName: fileName.replace(fileName.split('.').pop(), mime.extension(data))
              });
            })
          });
        }
      }
    });

    next();
  }
};

document.register.attributes = {
  name: 'doc',
  version: '0.0.1'
};

module.exports = document;