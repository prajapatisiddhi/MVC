$(function(){

    $("form").on("submit", function(e){ //form tag par submit event listner add karyo 6e jayere user save karse tyare aa function call back thase
        let valid = true; //form valid 6e ke nyi te check karse koi validation fail thase to te false kari dese
        $(".js-error").remove(); //koi duplicate error hoy pela thi tene remove kare e
        $("input, select, textarea").css("border","");

        function showError($field, message, isGroup=false){ // aek function banayu helper 
            //$field jquery no object banayo message error text isGroup radio/checkbox jeva groupt field mate
            valid = false;

            if(isGroup){//radio/checkbox mate
                $field.closest(".mb-3").append(
                    `<div class="text-danger mt-1 js-error">${message}</div>`
                );
                $field.closest(".mb-3").find("label, .form-check").css("color","red"); 
            } else {
                //single field like number , text , textarea
                $field.css("border","1px solid red");
                $field.closest(".mb-3").append(
                    `<div class="text-danger mt-1 js-error">${message}</div>`
                );
            }
        }

        //php ma aapelo aek json 6e je badhi entry par loop fervse
        $.each(window.formConfig, function(name, config){ //field name
            let $f = $(`[name='${name}'], [name='${name}[]']`);
            let rules = config.rules || {}; //rules object na hoy to te empty thayi jase
            let val = "";

            if($f.is(":radio")){
                //radio button mate /name selected 6e to and non selected to () empty
                val = $(`[name='${name}']:checked`).val() || "";
            }
            else if($f.is(":checkbox")){
                val = $(`[name='${name}[]']:checked`).map(function(){return this.value;}).get();
            }
            else if($f.attr("type")==="file"){
                val = $f[0].files.length ? $f[0].files[0] : null; //file choose kari 6e to peli file no object otherwise null
            }
            else{
                val = $f.val() ? $f.val().trim() : "";
            }

            // required
            if(rules.required){
                if($f.is(":checkbox") && val.length===0){
                    showError($f.first(), `${config.label} is required`, true);
                } else if($f.is(":radio") && !val){
                    showError($f.first(), `${config.label} is required`, true);
                } else if($f.attr("type")==="file" && !val){
                    showError($f, `${config.label} is required`);
                } else if(!$f.is(":checkbox") && !$f.is(":radio") && $f.attr("type")!=="file" && !val){
                    showError($f, `${config.label} is required`);
                }
            }

            // minlength
            if(rules.minlength && val && val.length < rules.minlength){
                showError($f, `${config.label} must be at least ${rules.minlength} characters`);
            }

            // numeric + min
            if(rules.numeric && val){
                if(isNaN(val)){
                    showError($f, `${config.label} must be a number`);
                } else if(rules.min && Number(val) < rules.min){
                    showError($f, `${config.label} must be greater than ${rules.min}`);
                }
            }

            // email
            if(rules.email && val){
                let pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if(!pattern.test(val)){
                    showError($f, "Invalid email format");
                }
            }

            // filetypes
            if(rules.filetypes && val){
                if($.inArray(val.type, rules.filetypes) === -1){
                    showError($f, `${config.label} must be ${rules.filetypes.join(", ")}`);
                }
            }
        });

        if(!valid) e.preventDefault();
    });

});


