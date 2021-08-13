/*
 * This file is part of justoverclock/flarum-ext-purify.
 *
 * Copyright (c) 2021 Marco Colia.
 * https://flarum.it
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

import app from 'flarum/forum/app';

export default function () {
  String.prototype.repeat = function (num) {
    return new Array(num + 1).join(this);
  };

  var FlarumPurify = app.forum.attribute('addItemToArray').split(',');
  if (FlarumPurify === ""){
    return;
  } else {
    $('p').html(function (i, txt) {
      // reiteriamo per tutte le parole
      for (var i = 0; i < FlarumPurify.length; i++) {
        var pattern = new RegExp('\\b' + FlarumPurify[i] + '\\b', 'gi');
        // Creazione della stringa di '*' equivalente alla lunghezza della parola stessa
        var replacement = '*'.repeat(FlarumPurify[i].length);

        txt = txt.replace(pattern, replacement);
      }
      return txt;
    });
  }
  if (app.forum.attribute('AlsoEmail') === true) {
    const p = this.$('p');
    const HideEmail = /([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9_-]+)/gi;

    p.each((index, element) => {
      $(element)
        .html($(element)
          .html().replace(HideEmail, '******@*****'));
    });
  }
  if (app.forum.attribute('CustomRegexp') === true) {
    const p = this.$('p');
    const CustomRegexP = new RegExp('' + app.forum.attribute('regexcustom'), 'gi');
    const obscureMe = app.translator.trans('flarum-ext-purify.forum.obscure');

    p.each((index, element) => {
      $(element)
        .html($(element)
          .html().replace(CustomRegexP, (match) => ` ${obscureMe}`))
    });
  }
}
