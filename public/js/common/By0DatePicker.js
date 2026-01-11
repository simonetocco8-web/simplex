

var By0DatePicker = new Class({
    Extends: Picker.Date,
    setMinLimit: function(minDate){
        // Set the min and max dates as Date objects
        if (!(minDate instanceof Date)) minDate = Date.parse(minDate);
        this.options.minDate = minDate;
        this.options.minDate.clearTime();
        
        this.date = this.limitDate(new Date(), this.options.minDate, this.options.maxDate);
    },
    setMaxLimit: function(maxDate){
        if (!(maxDate instanceof Date)) maxDate = Date.parse(maxDate);
        this.options.maxDate = maxDate;
        this.options.maxDate.clearTime();
        
        this.date = this.limitDate(new Date(), this.options.minDate, this.options.maxDate);
    },
    limitDate: function(date, min, max){
        if (min && date < min) return min;
        if (max && date > max) return max;
        return date;
    }
});