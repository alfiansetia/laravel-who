<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="generator" content="PhpSpreadsheet, https://github.com/PHPOffice/PhpSpreadsheet">
    <meta name="author" content="ASA" />
    <style type="text/css">
        html {
            font-family: Calibri, Arial, Helvetica, sans-serif;
            font-size: 11pt;
            background-color: white
        }

        a.comment-indicator:hover+div.comment {
            background: #ffd;
            position: absolute;
            display: block;
            border: 1px solid black;
            padding: 0.5em
        }

        a.comment-indicator {
            background: red;
            display: inline-block;
            border: 1px solid black;
            width: 0.5em;
            height: 0.5em
        }

        div.comment {
            display: none
        }

        table {
            border-collapse: collapse;
            page-break-after: always
        }

        .gridlines td {
            border: 1px dotted black
        }

        .gridlines th {
            border: 1px dotted black
        }

        .b {
            text-align: center
        }

        .e {
            text-align: center
        }

        .f {
            text-align: right
        }

        .inlineStr {
            text-align: left
        }

        .n {
            text-align: right
        }

        .s {
            text-align: left
        }

        td.style0 {
            vertical-align: bottom;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            color: #000000;
            font-family: 'Calibri';
            font-size: 10pt;
            background-color: white
        }

        th.style0 {
            vertical-align: bottom;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            color: #000000;
            font-family: 'Calibri';
            font-size: 10pt;
            background-color: white
        }

        td.style1 {
            vertical-align: middle;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #FF0000;
            font-family: 'Calibri Light';
            font-size: 22pt;
            background-color: white
        }

        th.style1 {
            vertical-align: middle;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #FF0000;
            font-family: 'Calibri Light';
            font-size: 22pt;
            background-color: white
        }

        td.style2 {
            vertical-align: middle;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #000000;
            font-family: 'Cambria';
            font-size: 18pt;
            background-color: white
        }

        th.style2 {
            vertical-align: middle;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #000000;
            font-family: 'Cambria';
            font-size: 18pt;
            background-color: white
        }

        td.style3 {
            vertical-align: middle;
            border-bottom: 3px double #000000 !important;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            color: #000000;
            font-family: 'Calibri';
            font-size: 10pt;
            background-color: white
        }

        th.style3 {
            vertical-align: middle;
            border-bottom: 3px double #000000 !important;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            color: #000000;
            font-family: 'Calibri';
            font-size: 10pt;
            background-color: white
        }

        td.style4 {
            vertical-align: middle;
            border-bottom: 3px double #000000 !important;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            color: #000000;
            font-family: 'Calibri';
            font-size: 24pt;
            background-color: white
        }

        th.style4 {
            vertical-align: middle;
            border-bottom: 3px double #000000 !important;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            color: #000000;
            font-family: 'Calibri';
            font-size: 24pt;
            background-color: white
        }

        td.style5 {
            vertical-align: middle;
            border-bottom: 3px double #000000 !important;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #FF0000;
            font-family: 'Calibri Light';
            font-size: 20pt;
            background-color: white
        }

        th.style5 {
            vertical-align: middle;
            border-bottom: 3px double #000000 !important;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #FF0000;
            font-family: 'Calibri Light';
            font-size: 20pt;
            background-color: white
        }

        td.style6 {
            vertical-align: middle;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 10pt;
            background-color: white
        }

        th.style6 {
            vertical-align: middle;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #000000;
            font-family: 'Arial';
            font-size: 10pt;
            background-color: white
        }

        td.style7 {
            vertical-align: middle;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: 2px solid #000000 !important;
            border-right: none #000000;
            color: #000000;
            font-family: 'Cambria';
            font-size: 14pt;
            background-color: white
        }

        th.style7 {
            vertical-align: middle;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: 2px solid #000000 !important;
            border-right: none #000000;
            color: #000000;
            font-family: 'Cambria';
            font-size: 14pt;
            background-color: white
        }

        td.style8 {
            vertical-align: middle;
            border-bottom: none #000000;
            border-top: 2px solid #000000 !important;
            border-left: 2px solid #000000 !important;
            border-right: 2px solid #000000 !important;
            font-weight: bold;
            color: #FF0000;
            font-family: 'Cambria';
            font-size: 22pt;
            background-color: white
        }

        th.style8 {
            vertical-align: middle;
            border-bottom: none #000000;
            border-top: 2px solid #000000 !important;
            border-left: 2px solid #000000 !important;
            border-right: 2px solid #000000 !important;
            font-weight: bold;
            color: #FF0000;
            font-family: 'Cambria';
            font-size: 22pt;
            background-color: white
        }

        td.style9 {
            vertical-align: middle;
            border-bottom: 2px solid #000000 !important;
            border-top: none #000000;
            border-left: 2px solid #000000 !important;
            border-right: 2px solid #000000 !important;
            font-weight: bold;
            color: #FF0000;
            font-family: 'Cambria';
            font-size: 22pt;
            background-color: white
        }

        th.style9 {
            vertical-align: middle;
            border-bottom: 2px solid #000000 !important;
            border-top: none #000000;
            border-left: 2px solid #000000 !important;
            border-right: 2px solid #000000 !important;
            font-weight: bold;
            color: #FF0000;
            font-family: 'Cambria';
            font-size: 22pt;
            background-color: white
        }

        td.style10 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: 2px solid #000000 !important;
            border-top: none #000000;
            border-left: 2px solid #000000 !important;
            border-right: none #000000;
            color: #000000;
            font-family: 'Cambria';
            font-size: 14pt;
            background-color: white
        }

        th.style10 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: 2px solid #000000 !important;
            border-top: none #000000;
            border-left: 2px solid #000000 !important;
            border-right: none #000000;
            color: #000000;
            font-family: 'Cambria';
            font-size: 14pt;
            background-color: white
        }

        td.style11 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: 2px solid #000000 !important;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            color: #000000;
            font-family: 'Cambria';
            font-size: 14pt;
            background-color: white
        }

        th.style11 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: 2px solid #000000 !important;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            color: #000000;
            font-family: 'Cambria';
            font-size: 14pt;
            background-color: white
        }

        td.style12 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: 2px solid #000000 !important;
            border-top: none #000000;
            border-left: none #000000;
            border-right: 2px solid #000000 !important;
            color: #000000;
            font-family: 'Cambria';
            font-size: 14pt;
            background-color: white
        }

        th.style12 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: 2px solid #000000 !important;
            border-top: none #000000;
            border-left: none #000000;
            border-right: 2px solid #000000 !important;
            color: #000000;
            font-family: 'Cambria';
            font-size: 14pt;
            background-color: white
        }

        td.style13 {
            vertical-align: top;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #000000;
            font-family: 'Cambria';
            font-size: 22pt;
            background-color: white
        }

        th.style13 {
            vertical-align: top;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #000000;
            font-family: 'Cambria';
            font-size: 22pt;
            background-color: white
        }

        td.style14 {
            vertical-align: bottom;
            text-align: center;
            border-bottom: 2px solid #000000 !important;
            border-top: 2px solid #000000 !important;
            border-left: 2px solid #000000 !important;
            border-right: none #000000;
            font-weight: bold;
            color: #FF0000;
            font-family: 'Calibri';
            font-size: 72pt;
            background-color: white
        }

        th.style14 {
            vertical-align: bottom;
            text-align: center;
            border-bottom: 2px solid #000000 !important;
            border-top: 2px solid #000000 !important;
            border-left: 2px solid #000000 !important;
            border-right: none #000000;
            font-weight: bold;
            color: #FF0000;
            font-family: 'Calibri';
            font-size: 72pt;
            background-color: white
        }

        td.style15 {
            vertical-align: bottom;
            text-align: center;
            border-bottom: 2px solid #000000 !important;
            border-top: 2px solid #000000 !important;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #FF0000;
            font-family: 'Calibri';
            font-size: 72pt;
            background-color: white
        }

        th.style15 {
            vertical-align: bottom;
            text-align: center;
            border-bottom: 2px solid #000000 !important;
            border-top: 2px solid #000000 !important;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #FF0000;
            font-family: 'Calibri';
            font-size: 72pt;
            background-color: white
        }

        td.style16 {
            vertical-align: bottom;
            text-align: center;
            border-bottom: 2px solid #000000 !important;
            border-top: 2px solid #000000 !important;
            border-left: none #000000;
            border-right: 2px solid #000000 !important;
            font-weight: bold;
            color: #FF0000;
            font-family: 'Calibri';
            font-size: 72pt;
            background-color: white
        }

        th.style16 {
            vertical-align: bottom;
            text-align: center;
            border-bottom: 2px solid #000000 !important;
            border-top: 2px solid #000000 !important;
            border-left: none #000000;
            border-right: 2px solid #000000 !important;
            font-weight: bold;
            color: #FF0000;
            font-family: 'Calibri';
            font-size: 72pt;
            background-color: white
        }

        td.style17 {
            vertical-align: top;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #000000;
            font-family: 'Cambria';
            font-size: 18pt;
            background-color: white
        }

        th.style17 {
            vertical-align: top;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #000000;
            font-family: 'Cambria';
            font-size: 18pt;
            background-color: white
        }

        td.style18 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #000000;
            font-family: 'Cambria';
            font-size: 20pt;
            background-color: white
        }

        th.style18 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #000000;
            font-family: 'Cambria';
            font-size: 20pt;
            background-color: white
        }

        td.style19 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: 2px solid #000000 !important;
            font-weight: bold;
            color: #000000;
            font-family: 'Cambria';
            font-size: 20pt;
            background-color: white
        }

        th.style19 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: 2px solid #000000 !important;
            font-weight: bold;
            color: #000000;
            font-family: 'Cambria';
            font-size: 20pt;
            background-color: white
        }

        td.style20 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #000000;
            font-family: 'Cambria';
            font-size: 18pt;
            background-color: white
        }

        th.style20 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #000000;
            font-family: 'Cambria';
            font-size: 18pt;
            background-color: white
        }

        td.style21 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: 3px double #000000 !important;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #000000;
            font-family: 'Cambria';
            font-size: 12pt;
            background-color: white
        }

        th.style21 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: 3px double #000000 !important;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #000000;
            font-family: 'Cambria';
            font-size: 12pt;
            background-color: white
        }

        td.style22 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #000000;
            font-family: 'Cambria';
            font-size: 12pt;
            background-color: white
        }

        th.style22 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #000000;
            font-family: 'Cambria';
            font-size: 12pt;
            background-color: white
        }

        td.style23 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: 2px solid #000000 !important;
            border-left: 2px solid #000000 !important;
            border-right: none #000000;
            font-weight: bold;
            color: #000000;
            font-family: 'Calibri';
            font-size: 18pt;
            background-color: white
        }

        th.style23 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: 2px solid #000000 !important;
            border-left: 2px solid #000000 !important;
            border-right: none #000000;
            font-weight: bold;
            color: #000000;
            font-family: 'Calibri';
            font-size: 18pt;
            background-color: white
        }

        td.style24 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: 2px solid #000000 !important;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #000000;
            font-family: 'Calibri';
            font-size: 18pt;
            background-color: white
        }

        th.style24 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: 2px solid #000000 !important;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #000000;
            font-family: 'Calibri';
            font-size: 18pt;
            background-color: white
        }

        td.style25 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: 2px solid #000000 !important;
            border-left: none #000000;
            border-right: 2px solid #000000 !important;
            font-weight: bold;
            color: #000000;
            font-family: 'Calibri';
            font-size: 18pt;
            background-color: white
        }

        th.style25 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: 2px solid #000000 !important;
            border-left: none #000000;
            border-right: 2px solid #000000 !important;
            font-weight: bold;
            color: #000000;
            font-family: 'Calibri';
            font-size: 18pt;
            background-color: white
        }

        td.style26 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: 2px solid #000000 !important;
            border-right: none #000000;
            color: #000000;
            font-family: 'Cambria';
            font-size: 14pt;
            background-color: white
        }

        th.style26 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: 2px solid #000000 !important;
            border-right: none #000000;
            color: #000000;
            font-family: 'Cambria';
            font-size: 14pt;
            background-color: white
        }

        td.style27 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            color: #000000;
            font-family: 'Cambria';
            font-size: 14pt;
            background-color: white
        }

        th.style27 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            color: #000000;
            font-family: 'Cambria';
            font-size: 14pt;
            background-color: white
        }

        td.style28 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: 2px solid #000000 !important;
            color: #000000;
            font-family: 'Cambria';
            font-size: 14pt;
            background-color: white
        }

        th.style28 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: 2px solid #000000 !important;
            color: #000000;
            font-family: 'Cambria';
            font-size: 14pt;
            background-color: white
        }

        td.style29 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            color: #000000;
            font-family: 'Cambria';
            font-size: 14pt;
            background-color: white
        }

        th.style29 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            color: #000000;
            font-family: 'Cambria';
            font-size: 14pt;
            background-color: white
        }

        td.style30 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: 2px solid #000000 !important;
            color: #000000;
            font-family: 'Cambria';
            font-size: 14pt;
            background-color: white
        }

        th.style30 {
            vertical-align: middle;
            text-align: left;
            padding-left: 0px;
            border-bottom: none #000000;
            border-top: none #000000;
            border-left: none #000000;
            border-right: 2px solid #000000 !important;
            color: #000000;
            font-family: 'Cambria';
            font-size: 14pt;
            background-color: white
        }

        td.style31 {
            vertical-align: middle;
            text-align: center;
            border-bottom: 2px solid #000000 !important;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #FF0000;
            font-family: 'Cambria';
            font-size: 30pt;
            background-color: white
        }

        th.style31 {
            vertical-align: middle;
            text-align: center;
            border-bottom: 2px solid #000000 !important;
            border-top: none #000000;
            border-left: none #000000;
            border-right: none #000000;
            font-weight: bold;
            color: #FF0000;
            font-family: 'Cambria';
            font-size: 30pt;
            background-color: white
        }

        table.sheet0 col.col0 {
            width: 81.3333324pt
        }

        table.sheet0 col.col1 {
            width: 151.14444271pt
        }

        table.sheet0 col.col2 {
            width: 131.48888738pt
        }

        table.sheet0 col.col3 {
            width: 173.51110912pt
        }

        table.sheet0 tr {
            height: 12.75pt
        }

        table.sheet0 tr.row0 {
            height: 30pt
        }

        table.sheet0 tr.row1 {
            height: 30.75pt
        }

        table.sheet0 tr.row2 {
            height: 30pt
        }

        table.sheet0 tr.row3 {
            height: 101.25pt
        }

        table.sheet0 tr.row4 {
            height: 26.25pt
        }

        table.sheet0 tr.row5 {
            height: 26.25pt
        }

        table.sheet0 tr.row6 {
            height: 26.25pt
        }

        table.sheet0 tr.row7 {
            height: 26.25pt
        }

        table.sheet0 tr.row8 {
            height: 9.75pt
        }

        table.sheet0 tr.row9 {
            height: 19.5pt
        }

        table.sheet0 tr.row10 {
            height: 20.25pt
        }

        table.sheet0 tr.row11 {
            height: 18.75pt
        }

        table.sheet0 tr.row12 {
            height: 17.25pt
        }

        table.sheet0 tr.row13 {
            height: 18.75pt
        }

        table.sheet0 tr.row14 {
            height: 38.25pt
        }

        table.sheet0 tr.row15 {
            height: 23.25pt
        }

        table.sheet0 tr.row16 {
            height: 26.25pt
        }

        table.sheet0 tr.row17 {
            height: 26.25pt
        }

        table.sheet0 tr.row18 {
            height: 18.75pt
        }

        table.sheet0 tr.row19 {
            height: 13.5pt
        }

        table.sheet0 tr.row20 {
            height: 93pt
        }
    </style>
</head>

<body>
    <style>
        @page {
            margin-left: 0.14in;
            margin-right: 0.13in;
            margin-top: 0.12in;
            margin-bottom: 0.2in;
        }

        body {
            margin-left: 0.14in;
            margin-right: 0.13in;
            margin-top: 0.12in;
            margin-bottom: 0.2in;
        }
    </style>
    <table border="0" cellpadding="0" cellspacing="0" id="sheet0" class="sheet0 gridlines">
        <col class="col0">
        <col class="col1">
        <col class="col2">
        <col class="col3">
        <tbody>
            <tr class="row0">
                <td class="column0 style18 s style19" colspan="3">KEPADA YTH,</td>
                <td class="column3 style8 s">{{ $data->ekspedisi }}</td>
            </tr>
            <tr class="row1">
                <td class="column0 style13 f style13" colspan="3" rowspan="2">{{ strtoupper($data->tujuan) }}</td>
                <td class="column3 style9 s">{{ $data->koli }} Koli</td>
            </tr>
            <tr class="row2">
                <td class="column3 style1 null"></td>
            </tr>
            <tr class="row3">
                <td class="column0 style17 s style17" colspan="4">{{ $data->alamat }}</td>
            </tr>
            <tr class="row4">
                <td class="column0 style2 s">Up</td>
                <td class="column1 style18 s style18" colspan="3">: {{ $data->up }} </td>
            </tr>
            <tr class="row5">
                <td class="column0 style2 s">Tlp</td>
                <td class="column1 style20 s style20" colspan="3">: {{ $data->telp }}</td>
            </tr>
            <tr class="row6">
                <td class="column0 style2 s">No. DO</td>
                <td class="column1 style20 s style20" colspan="3">: {{ $data->do }} ( {{ $data->epur }} )</td>
            </tr>
            <tr class="row7">
                <td class="column0 style2 s">Untuk</td>
                <td class="column1 style20 s style20" colspan="3">: {{ $data->untuk }}</td>
            </tr>
            <tr class="row8">
                <td class="column0 style3 null"></td>
                <td class="column1 style3 null"></td>
                <td class="column2 style4 null"></td>
                <td class="column3 style5 null"></td>
            </tr>
            <tr class="row9">
                <td class="column0 style6 s">FROM :</td>
                <td class="column1 style21 s style21" colspan="3">PT. MITRA ASA PRATAMA</td>
            </tr>
            <tr class="row10">
                <td class="column0 style6 null"></td>
                <td class="column1 style22 s style22" colspan="3">MT.HARYONO SQUARE , JL. MT.HARYONO</td>
            </tr>
            <tr class="row11">
                <td class="column0 style6 null"></td>
                <td class="column1 style22 s style22" colspan="3">KAV.10 NO: OF 01/06 BIDARA CINA</td>
            </tr>
            <tr class="row12">
                <td class="column0 style6 null"></td>
                <td class="column1 style22 s style22" colspan="3">JATINEGARA - JAKARTA TIMUR</td>
            </tr>
            <tr class="row13">
                <td class="column0 style6 null"></td>
                <td class="column1 style22 s style22" colspan="3">TELP. 021 - 89456598 / 021- 29067256 ~ 57 / FAX.
                    021 - 29067258</td>
            </tr>
            <tr class="row14">
                <td class="column0 style31 s style31" colspan="4">HATI - HATI BARANG MUDAH PENYOK</td>
            </tr>
            <tr class="row15">
                <td class="column0 style23 s style25" colspan="4">&nbsp;PACKING LIST :</td>
            </tr>
            @foreach ($data->detail as $item)
                <tr class="row16">
                    <td class="column0 style26 s style28" colspan="4">~ {{ $item->product->code }}
                        ({{ $item->product->name }})
                        = {{ $item->qty }} Box
                    </td>
                </tr>
                <tr class="row17">
                    <td class="column0 style7 null"></td>
                    <td class="column1 style29 s style30" colspan="3">Lot : {{ $item->lot }}</td>
                </tr>
            @endforeach
            <tr class="row18">
                <td class="column0 style10 s style12" colspan="4">( Nilai Barang ~ Rp. {{ $data->nilai }} )</td>
            </tr>
            <tr class="row19">
                <td class="column0">&nbsp;</td>
                <td class="column1">&nbsp;</td>
                <td class="column2">&nbsp;</td>
                <td class="column3">&nbsp;</td>
            </tr>
            <tr class="row20">
                <td class="column0 style14 s style16" colspan="4">SURAT JALAN/DO</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
