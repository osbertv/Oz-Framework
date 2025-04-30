    </div>
    <?=$this->include('helper/flash')?>
    <script type="text/javascript">
      //<![CDATA[
        $(document).ready(function(){
          $('.combobox').combobox({bsVersion: '4'});
          $('.toast').toast('show');
        });
      //]]>
    </script>
    <div id='divLoadingStatus' class="ui-state-highlight ui-corner-all loading1" style="height:35px; top:3px; left:230px; width: 250px; position: absolute">
        <div style="position: relative; top: 8px; font-weight: bold">Loading. Please wait...</div>
        <script type="text/javascript">$('#divLoadingStatus').hide();</script>
    </div>
  </body>
</html>
