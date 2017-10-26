<style type="text/css">
  .clearfix {
    clear: both;
  }
  .single-lab_directory_staff {
    float: left;
    width: 25%;
    text-align: center;
    padding: 0px 10px;
  }
  .single-lab_directory_staff .photo {
    margin-bottom: 5px;
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
        </div>

    [/lab_directory_staff_loop]

</div>
