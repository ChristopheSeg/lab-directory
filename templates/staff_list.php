<style type="text/css">
  .clearfix {
    clear: both;
  }
  .single-lab_directory_staff {
    margin-bottom: 50px;
  }
  .single-lab_directory_staff .photo {
    float: left;
    margin-right: 15px;
  }
  .single-lab_directory_staff .photo img {
    max-width: 100px;
    height: auto;
  }
  .single-lab_directory_staff .name {
    font-size: 1em;
    line-height: 1em;
    margin-bottom: 4px;
  }
  .single-lab_directory_staff .position {
    font-size: .9em;
    line-height: .9em;
    margin-bottom: 10px;
  }
  .single-lab_directory_staff .bio {
    margin-bottom: 8px;
  }
  .single-lab_directory_staff .email {
    font-size: .9em;
    line-height: .9em;
    margin-bottom: 10px;
  }
  .single-lab_directory_staff .phone {
    font-size: .9em;
    line-height: .9em;
  }
  .single-lab_directory_staff .website {
    font-size: .9em;
    line-height: .9em;
  }
</style>
<div id="lab-directory-wrapper">

    [lab_directory_staff_loop]

        <div class="single-lab_directory_staff">
                [photo]
            <div class="name">
                [name]
            </div>
            <div class="position">
                [position]
            </div>
            <div class="bio">
                [bio]
            </div>
                [email]
                [phone_number]
                [website]
            <div class="clearfix"></div>
        </div>

    [/lab_directory_staff_loop]

</div>
