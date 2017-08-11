jQuery(document).ready(function($) {
		$(document).ready(function() {
			$('#btnAdd').click(function() {
                            console.log('Click Add');
				var num		= $('.clonedInput').length;	// how many "duplicatable" input fields we currently have
				var newNum	= new Number(num + 1);		// the numeric ID of the new input field being added

				// create the new element via clone(), and manipulate it's ID using newNum value
				var newElem = $('#input' + num).clone().attr('id', 'input' + newNum);
				
				// manipulate the name/id values of the input inside the new element
				newElem.find('.first_name').attr('id', 'first_name' + newNum).val('');
				newElem.find('.last_name').attr('id', 'last_name' + newNum).val('');
				newElem.find('.prefix').attr('id', 'prefix' + newNum).val('');
				newElem.find('.mobile').attr('id', 'mobile' + newNum).val('');
				newElem.find('.date_of_birth').attr('id', 'date_of_birth' + newNum).val('');
				newElem.find('.date_of_birth').removeClass("hasDatepicker");
				newElem.find('.date_of_birthx').attr('id', 'date_of_birthx' + newNum).val('');
				newElem.find('.date_of_birthx').removeClass("hasDatepicker");
				newElem.find('.email').attr('id', 'email' + newNum).val('');
				newElem.find('.sex').attr('id', 'sex' + newNum).val('');
                newElem.find('.member_type_id').attr('id', 'member_type_id' + newNum).val('');
                newElem.find('.people_type_id').attr('id', 'people_type_id' + newNum).val('');
                newElem.find('.small_group_id').attr('id', 'small_group_id' + newNum).val('');
				// insert the new element after the last "duplicatable" input field
				$('#input' + num).after(newElem);
				
				
				// enable the "remove" button
				$('#btnDel').prop( "disabled", false );

				// business rule: you can only add 50 names
				if (newNum == 50)
					$('#btnAdd').prop( "disabled", true );
			});

			$('#btnDel').click(function() {
				var num	= $('.clonedInput').length;	// how many "duplicatable" input fields we currently have
				$('#input' + num).remove();		// remove the last element

				// enable the "add" button
				$('#btnAdd').prop( "disabled", false );

				// if only one element remains, disable the "remove" button
				if (num-1 == 1)
					$('#btnDel').prop( "disabled", true);
			});

			$('#btnDel').prop( "disabled", "disabled" );
		});
});


