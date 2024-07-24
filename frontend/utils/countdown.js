/**
 * This function returns an object of time remaining between start and end
 * @param {date} start A date object for the starting time
 * @param {date} end A date object for the time to count down to
 * @returns {Object} An object that looks like this {'days': 59, 'hours': 23, 'seconds': 59}
 */
import {
    addDays,
    addHours,
    addMinutes,
    differenceInDays,
    differenceInHours,
    differenceInMinutes,
    differenceInSeconds,
} from "date-fns";

function countdown(start, end) {
    let x = start;
    let y = end;

    let output = {};
    let temp;

    temp = differenceInDays(y, x);
    output.days = temp;

    x = addDays(x, temp);
    temp = differenceInHours(y, x);
    output.hours = temp;

    x = addHours(x, temp);
    temp = differenceInMinutes(y, x);
    output.minutes = temp;

    x = addMinutes(x, temp);
    temp = differenceInSeconds(y, x);
    output.seconds = temp;

    return output;
}

export default countdown;
