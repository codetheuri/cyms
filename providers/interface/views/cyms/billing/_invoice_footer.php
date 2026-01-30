<table width="100%" style="border-top: 1px solid #eee; padding-top: 10px; font-size: 8pt; color: #95a5a6;">
    <tr>
        <td width="33%">Printed on <?= date('d/m/Y H:i') ?></td>
        <td width="33%" align="center">Page {PAGENO} of {nbpg}</td>
        <td width="33%" align="right">Software by <?= $_SERVER['APP_CODE'] ?? 'yiisoft' ?>?></td>
    </tr>
</table>