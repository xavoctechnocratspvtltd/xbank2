<?php



class page_tests_OccupationCorrect extends Page {
	function init(){
		parent::init();

		$occupations_dist = $this->add('Model_Member')->_dsql()->del('fields')->field('DISTINCT(Occupation)')->field('COUNT(*) as Records')->group('Occupation')->get();
		// var_dump($occupations_dist);

		$form= $this->add('Form');

		$form->addField('Line','new_name');
		foreach ($occupations_dist as $occ) {
			$form->addField('Checkbox',$this->app->normalizeName($occ['Occupation']),$occ['Occupation'].' '. $occ['Records']);
		}

		$form->addSubmit('UPDATE');

		if($form->isSubmitted()){
			$checked=[];
			foreach ($occupations_dist as $occ) {
				if($form[$this->app->normalizeName($occ['Occupation'])]){
					$checked[] = $occ['Occupation'];
				}
			}

			$this->add('Model_Member')->_dsql()->set('Occupation',$form['new_name'])->where('Occupation','in',$checked)->debug()->update();

			// var_dump($checked);
		}
	}
}