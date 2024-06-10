/**
 * Created by Stefan Hariton on 6/18/16.
 */

"use strict";

var shortId = require('shortid');
var fs = require('fs');

const defaultDir = __dirname + '/../../uploads/';

function save(file) {
  if (file) {
    var path = defaultDir + shortId.generate() + file.hapi.filename;
    var f = fs.createWriteStream(path);

    f.on('error', function(error) {
      console.error(error);
    });

    file.pipe(f);

    file.on('end', function (err) {
      if (err) {
        console.error(err);
      }
      return path;
    });

    return path;
  }
}

module.exports = save;