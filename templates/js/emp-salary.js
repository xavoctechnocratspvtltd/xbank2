$.each({
  netpayable: function (netpayable,salary=0,allow_paid=0,ded=0,pf_amount=0,incentive_amount){
    // alert();
    $(netpayable).val(parseInt($(salary).val()) + parseInt($(allow_paid).val()) - parseInt($(ded).val()) - parseInt($(pf_amount).val()) + parseInt($(incentive_amount).val())  );
    $(netpayable).parent('.atk-col-1').find('.value-text').text(parseInt($(salary).val()) + parseInt($(allow_paid).val()) - parseInt($(ded).val()) - parseInt($(pf_amount).val()) + parseInt($(incentive_amount).val()) );
  },

  salary: function (salary,basic_salary,total_days,paid_days){
    value = parseInt($(basic_salary).val()) / parseInt($(total_days).val()) * parseInt($(paid_days).val());
    value = parseFloat(value).toFixed(0);

    $(salary).val(value); 
    $(salary).parent('.atk-col-1').find('.value-text').text(value);
  },

  allowPaid: function (allow_paid,paid_days,total_days,other_allow){
    allow_value = parseInt($(paid_days).val()) / parseInt($(total_days).val()) * parseInt($(other_allow).val());
    allow_value = parseFloat(allow_value).toFixed(0);
    
    $(allow_paid).val(allow_value);
    
  },
  workingDays: function (t_day,w_day){
    $(t_day).val( parseInt($(w_day).val()));
    $(t_day).parent('.atk-col-1').find('.value-text').text(parseInt($(w_day).val()));
    
  },

  totalDayInMonth: function (total_day_field,m,y,t_day){
    var x = new Date($(y).val(),$(m).val(),1,-1).getDate();

    $(total_day_field).val(x);
    $.univ().workingDays(t_day,total_day_field);
    // alert(x);
    // $(d).parent('.atk-col-1').find('.value-text').text(parseInt($(t_w_f).val()));
  },

  weeklyOff: function (wf,t_w_f){
    $(wf).val( parseInt($(t_w_f).val()));
    $(wf).parent('.atk-col-1').find('.value-text').text(parseInt($(t_w_f).val()));
  },

  dayInMonth: function (tmd,mid){
    $(tmd).val( parseInt($(mid).val()));
    $(tmd).parent('.atk-col-1').find('.value-text').text(parseInt($(mid).val()));
  },

  pfSalary:function(pf_salary,salary,deduct){
  	salary_value = 0;
  if(deduct){
    salary_value = parseInt($(salary).val());
  }
  $(pf_salary).val(salary_value);
  $(pf_salary).parent('.bank-col-1').find('.value-text').text(salary_value);
  },

  pfAmount: function (pf_amount,salary,deduct){
    salary_value = 0;
    if(deduct){
     salary_value = parseInt($(salary).val()) * 12 / 100 ;
     salary_value = parseFloat(salary_value).toFixed(0);
    }
    $(pf_amount).val(salary_value);
  }


},$.univ._import);