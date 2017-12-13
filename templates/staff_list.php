<style type="text/css">
  .clearfix {
    clear: both;
  }
  .single-lab_directory_staff {
    margin-bottom: 50px;
  }
  .single-lab_directory_staff .ld_photo {
    float: left;
    margin-right: 15px;
  }
  .single-lab_directory_staff .ld_photo img {
    max-width: 100px;
    height: auto;
  }
  .single-lab_directory_staff .ld_name {
    font-size: 1em;
    line-height: 1em;
    margin-bottom: 4px;
  }
  .single-lab_directory_staff .ld_position {
    font-size: .9em;
    line-height: .9em;
    margin-bottom: 10px;
  }
  .single-lab_directory_staff .ld_bio {
    margin-bottom: 8px;
  }

</style>
<div id="lab-directory-wrapper">

    [lab_directory_staff_loop]
        <div class="single-lab_directory_staff">
                <div class="ld_photo">[ld_photo]</div>
            	<div class="ld_name" >[ld_name]</div> 
				<div class="ld_name" >[ld_position]</div>
                <div class="ld_name" >[ld_mails]</div>
                <div class="ld_name" >[ld_phone_number]</div>
                <div class="ld_name" >[ld_webpage]</div>
                <div class="ld_name" >[ld_bio]</div>
            <div class="clearfix"></div>
        </div>
    [/lab_directory_staff_loop]

</div>
