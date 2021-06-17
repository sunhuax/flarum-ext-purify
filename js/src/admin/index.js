/*
 * This file is part of justoverclock/flarum-ext-purify.
 *
 * Copyright (c) 2021 Marco Colia.
 * https://flarum.it
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

import app from 'flarum/app';

app.initializers.add('justoverclock/flarum-ext-purify', () => {
  app.extensionData.for('justoverclock-purify').registerSetting({
    setting: 'justoverclock-purify.addItemToArray',
    name: 'addItemToArray',
    type: 'text',
    label: app.translator.trans('flarum-ext-purify.admin.additem'),
    help: app.translator.trans('flarum-ext-purify.admin.additemdesc'),
    placeholder: 'word1,word2,word3',
  });
  app.extensionData.for('justoverclock-purify').registerSetting({
    setting: 'justoverclock-purify.AlsoEmail',
    label: app.translator.trans('flarum-ext-purify.admin.hidemail'),
    type: 'boolean',
  });
});
