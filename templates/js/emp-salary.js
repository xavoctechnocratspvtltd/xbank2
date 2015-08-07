$.each({
	
  netpayable: function (netpayable,salary=0,allow_paid=0,ded=0,pf_amount=0){
    // alert();
    $(netpayable).val(parseInt($(salary).val()) + parseInt($(allow_paid).val()) - parseInt($(ded).val()) - parseInt($(pf_amount).val())  );
  	$(netpayable).parent('.atk-col-1').find('.value-text').text(parseInt($(salary).val()) + parseInt($(allow_paid).val()) - parseInt($(ded).val()) - parseInt($(pf_amount).val()));
  },

  salary: function (salary,basic_salary,total_days,paid_days){
  	$(salary).val(parseInt($(basic_salary).val()) / parseInt($(total_days).val()) * parseInt($(paid_days).val())); 
  	$(salary).parent('.atk-col-1').find('.value-text').text(parseInt($(basic_salary).val()) / parseInt($(total_days).val()) * parseInt($(paid_days).val()));
  },

  allowPaid: function (allow_paid,paid_days,total_days,other_allow){
  	$(allow_paid).val( parseInt($(paid_days).val()) / parseInt($(total_days).val()) * parseInt($(other_allow).val()) );
  },
  workingDays: function (t_day,w_day){
  	$(t_day).val( parseInt($(w_day).val()));
  	$(t_day).parent('.atk-col-1').find('.value-text').text(parseInt($(w_day).val()));
  },

  pfSalary:function(pf_salary,salary){
  	$(pf_salary).val( parseInt($(salary).val()));
  	$(pf_salary).parent('.atk-col-1').find('.value-text').text(parseInt($(salary).val()));
  },

  pfAmount: function (pf_amount,salary){
  	$(pf_amount).val(parseInt($(salary).val()) * 12 / 100   );
  }


},$.univ._import);