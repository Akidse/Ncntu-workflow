(function($){
  jQuery.fn.departmentSelect = function()
  {
  	var parse = function()
  	{
  		if(this.tagName != "SELECT")
  		{
  			$(this).find("select").each(parse);
  		}
  		else constructSelect(this);
  	}

  	var constructSelect = function(select)
  	{
  		$(select).attr("hidden", true);
  		$(select).parent().append("<select name='"+$(select).attr('name')+"' hidden='true'><option value='0' selected></option></select>");
  		var trueSelect = $(select).parent().children('select').children('option');

  		$(select).attr('name', $(select).attr('name')+"_fake");
  		if(!$(select).parent().hasClass("form-group"))
  		{
  			$(select).wrap("<div class='form-group'></div>")
  		}

  		$(select).parent().on("change", "select", function()
		{
			if($(this).val() == 0)
			{
				$(trueSelect).attr("value", $(this).parent().parent().children('select').val() || 0);
				$(this).parent().children(".form-group").remove();
			}
			else
			{
				$(trueSelect).attr("value", $(this).val());
				createSubSelect($(this).val(), this);
			}

			$(trueSelect).trigger("onDepartmentChange");
		});
		if($(select).data("selected-option") != 0)
		{
			fillSelectHierarchy($(select).data("selected-option"), select);
		}
		else
		{
			fillSelectHierarchy(0, select);
		}
  	}

  	var fillSelect = function(department_id, select, selected)
  	{
  		var select = $(select);
		$.post("/api/departments/getlist", {'subdepartment_id' : department_id}, function(data)
		{
			departments = jQuery.parseJSON(data);
			if(departments.length == 0)
			{
				select.remove();
				return;
			}
			select.append($('<option/>', {
				value: 0,
				text: '-',
			}));
			for(var i = 0; i < departments.length; i++)
			{
				select.append($('<option/>', {
					value: departments[i].department_id,
					text: departments[i].name,
					selected: (selected == departments[i].department_id ? true : false)
				}));
			}
		});
  	}
  	var createSubSelect = function(department_id, block, selected)
	{
		$(block).parent().children("div").remove();
		$(block).parent().append('<div class="form-group"><select class="form-control" name="department_select_'+department_id+'"></select></div>');
		var select = $(block).parent().children(".form-group").children("select");
		fillSelect(department_id, select, selected);
	}

	var fillSelectHierarchy = function(department_id, select)
	{
		$.post("/api/departments/getparentslist", {'department_id' : department_id}, function(data)
		{
			departments = jQuery.parseJSON(data).reverse();
			for(var i = 0; i < departments.length; i++)
			{
				if(i < departments.length - 1)createSubSelect(departments[i], select, departments[i+1]);
				else createSubSelect(departments[i], select);

				select = $(select).parent().children(".form-group").children("select");
			}
		});
	}
  	return this.each(parse);
  };
})(jQuery);