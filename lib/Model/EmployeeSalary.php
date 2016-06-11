<?php
class Model_EmployeeSalary extends Model_Table{
	public $table='employee_salary_record';
	function init(){
		parent::init();
		$month=array( '01'=>"Jan",'02'=>"Feb",'03'=>"March",'04'=>"April",
					'05'=>"May",'06'=>"Jun",'07'=>"July",'08'=>"Aug",'09'=>"Sep",
					'10'=>"Oct",'11'=>"Nov",'12'=>"Dec");

		$date=$this->api->today;
		$y=date('Y',strtotime($date));	
		for ($i=$y; $i >=1970 ; $i--) { 
			$years[]=$i;
		}

		$this->hasOne('Branch','branch_id');
		$this->hasOne('Employee','employee_id');

		$this->addExpression('employee_code')->set(function($m,$q){
			return $m->refSQL('employee_id')->fieldQuery('emp_code');
		});

		$this->addExpression('name')->set(function($m,$q){
			return $m->refSQL('employee_id')->fieldQuery('name');
		})->caption('Employee name');
		$this->addExpression('basic_salary')->set(function($m,$q){
			return $m->refSQL('employee_id')->fieldQuery('basic_salary');
		});
		$this->addExpression('other_allowance')->set(function($m,$q){
			return $m->refSQL('employee_id')->fieldQuery('other_allowance');
		});
		$this->addExpression('is_active')->set(function($m,$q){
			return $m->refSQL('employee_id')->fieldQuery('is_active');
		});

		$this->addExpression('salary_date')->set(function($m,$q){
			return $q->expr('LAST_DAY(CONCAT([0],"-",[1],"-01"))',
					array(
						$m->getElement('year'),
						$m->getElement('month')
					)
				);
		});


		
		$this->addField('month')->setValueList($month);
		$this->addField('year')->setValueList($years);
		$this->addField('CL');
		$this->addField('CCL');
		$this->addField('LWP');
		$this->addField('ABSENT');
		$this->addField('monthly_off');
		$this->addField('total_days')->defaultValue(0);
		$this->addField('paid_days')->defaultValue(0);
		$this->addField('leave')->Caption('Total Leave')->defaultValue(0);
		$this->addField('salary')->defaultValue(0);
		$this->addField('pf_salary')->defaultValue(0);
		$this->addField('ded')->defaultValue(0);
		$this->addField('pf_amount')->defaultValue(0);
		$this->addField('incentive')->defaultValue(0);
		$this->addField('allow_paid')->defaultValue(0);
		$this->addField('net_payable')->defaultValue(0);
		$this->addField('narration')->type('text')->defaultValue(0);
		$this->addField('total_month_day');
		$this->addHook('beforeSave',$this);

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
	}

	function convert_number_to_words($number) {
	    $hyphen      = '-';
	    $conjunction = ' and ';
	    $separator   = ', ';
	    $negative    = 'negative ';
	    $decimal     = ' point ';
	    $dictionary  = array(
	        0                   => 'zero',
	        1                   => 'one',
	        2                   => 'two',
	        3                   => 'three',
	        4                   => 'four',
	        5                   => 'five',
	        6                   => 'six',
	        7                   => 'seven',
	        8                   => 'eight',
	        9                   => 'nine',
	        10                  => 'ten',
	        11                  => 'eleven',
	        12                  => 'twelve',
	        13                  => 'thirteen',
	        14                  => 'fourteen',
	        15                  => 'fifteen',
	        16                  => 'sixteen',
	        17                  => 'seventeen',
	        18                  => 'eighteen',
	        19                  => 'nineteen',
	        20                  => 'twenty',
	        30                  => 'thirty',
	        40                  => 'fourty',
	        50                  => 'fifty',
	        60                  => 'sixty',
	        70                  => 'seventy',
	        80                  => 'eighty',
	        90                  => 'ninety',
	        100                 => 'hundred',
	        1000                => 'thousand',
	        1000000             => 'million',
	        1000000000          => 'billion',
	        1000000000000       => 'trillion',
	        1000000000000000    => 'quadrillion',
	        1000000000000000000 => 'quintillion'
	    );

	    if (!is_numeric($number)) {
	        return false;
	    }

	    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
	        // overflow
	        trigger_error(
	            'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
	            E_USER_WARNING
	        );
	        return false;
	    }

	    if ($number < 0) {
	        return $negative . $this->convert_number_to_words(abs($number));
	    }

	    $string = $fraction = null;

	    if (strpos($number, '.') !== false) {
	        list($number, $fraction) = explode('.', $number);
	    }

	    switch (true) {
	        case $number < 21:
	            $string = $dictionary[$number];
	            break;
	        case $number < 100:
	            $tens   = ((int) ($number / 10)) * 10;
	            $units  = $number % 10;
	            $string = $dictionary[$tens];
	            if ($units) {
	                $string .= $hyphen . $dictionary[$units];
	            }
	            break;
	        case $number < 1000:
	            $hundreds  = $number / 100;
	            $remainder = $number % 100;
	            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
	            if ($remainder) {
	                $string .= $conjunction .$this->convert_number_to_words($remainder);
	            }
	            break;
	        default:
	            $baseUnit = pow(1000, floor(log($number, 1000)));
	            $numBaseUnits = (int) ($number / $baseUnit);
	            $remainder = $number % $baseUnit;
	            $string = $this->convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
	            if ($remainder) {
	                $string .= $remainder < 100 ? $conjunction : $separator;
	                $string .= $this->convert_number_to_words($remainder);
	            }
	            break;
	    }

	    if (null !== $fraction && is_numeric($fraction)) {
	        $string .= $decimal;
	        $words = array();
	        foreach (str_split((string) $fraction) as $number) {
	            $words[] = $dictionary[$number];
	        }
	        $string .= implode(' ', $words);
	    }

	    return $string;
	}
}