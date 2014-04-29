$().ready(function(){
    $("input.data").mask("99/99/9999");
    $("input.telefone").mask("(99) 9999-9999");
    $("input.fone").mask("(99) 9999-9999");
    $("input.cpf").mask("999.999.999-99");
    $("input.cnpj").mask("99.999.999/9999-99");
    $("input.cep").mask("99.999-999");

    $('.data').datepicker({
        changeMonth: true,
        changeYear: true,
        showOn: 'button',
        buttonImage: baseUrl + '/imgs/calendar.gif',
        buttonImageOnly: true,
        regional: 'pt-BR'
    });
    
});
