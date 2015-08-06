$.each({
	
  netpayable: function (netpayable,salary=0,allow_paid=0,ded=0,pf_amount=0){
    // alert();
    $(netpayable).val(parseInt($(salary).val()) + parseInt($(allow_paid).val()) - parseInt($(ded).val()) - parseInt($(pf_amount).val())  );
  },

  salary: function (salary,basic_salary,total_days,paid_days){
  	$(salary).val(parseInt($(basic_salary).val()) / parseInt($(total_days).val()) * parseInt($(paid_days).val())); 
  }

},$.univ._import);