<?php
class Model_Memorandum_TransactionRow extends Model_Table {
	var $table = "memorandum_transactionsrow";

	function init() {
		parent::init();

		$this->hasOne('Memorandum_Transaction', 'memorandum_transaction_id')->display(['form' => 'autocomplete/Basic']);
		$this->hasOne('Account', 'account_id')->display(['form' => 'autocomplete/Basic']);

		$this->addField('tax')->setValueList(GST_VALUES);
		$this->addField('tax_percentage');
		$this->addField('tax_amount');
		$this->addField('tax_narration')->type('text'); //saving sub tax value in json format

		$this->addField('tax_excluded_amount')->type('text'); //saving sub tax value in json format
		$this->addField('amountCr')->type('money'); // tax included amount
		$this->addField('amountDr')->type('money'); // tax included amount

		$this->addField('created_at')->type('datetime')->defaultValue($this->app->now);
		$this->addExpression('memo_transaction_type')->set(function ($m, $q) {
			return $q->expr('[0]', [$m->refSQL('memorandum_transaction_id')->fieldQuery('memorandum_type')]);
		});
		$this->addHook('beforeSave', $this);
		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave() {

		// saving tax value with sub_tax value
		if ($this['tax']) {
			$temp = explode(" ", $this['tax']);
			$tax_name = $temp[0];
			$tax_percentage = $temp[1];
			$tax = (100 + $tax_percentage);
			$amount = $this['amountDr'];
			if ($this['amountCr'] > 0) {
				$amount = $this['amountCr'];
			}

			$tax_excluded_amount = round((($amount / $tax) * 100), 2);
			$tax_amount = round(($amount - $tax_excluded_amount), 2);

			$sub_tax = [];
			if ($tax_name === "GST") {
				$sgst = "SGST " . round(($tax_percentage / 2), 1) . "%";
				$cgst = "CGST " . round(($tax_percentage / 2), 1) . "%";

				$sub_tax[$sgst] = ($tax_amount / 2);
				$sub_tax[$cgst] = ($tax_amount / 2);
			}
			if ($tax_name === "IGST") {
				$sub_tax["IGST " . $tax_percentage . "%"] = $tax_amount;
			}

			$this['tax_amount'] = $tax_amount;
			$this['tax_excluded_amount'] = $tax_excluded_amount;
			$this['tax_percentage'] = $tax_percentage;
			$this['tax_narration'] = json_encode($sub_tax, true);
		}

	}

}