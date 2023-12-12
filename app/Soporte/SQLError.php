<?php

namespace App\Soporte;


class SQLError
{
    const APPEND_TABLE = 300; 

    /// <summary>
    /// Usado BaseDAL.GetWhereKeys(DataTable toCursor, int lnIndexRow, params DataField[] taKeys),
    /// o donde se precise armar las llaves para la clausula Where
    /// </summary>
    const WHERE_KEYS = 401;

    /// <summary>
    /// Codigo de Error sugerido en el metodo BaseDAL.InsertarDatos(string tcTabla, DataTable toCursor)
    /// </summary>
    const INSERTAR_DATOS = 500; 

    /// <summary>
    ///  Codigo de Error sugerido cuando se llama en try/catch al BadeDAL.InsertarDatos(...)
    /// </summary>
    const REGISTRAR_VOID = 501; 

    /// <summary>
    ///  Codigo de Error sugerido en el metodo BaseDAL.ActualizarDatosKeys(string tcTabla, DataTable toCursor, params DataField[] taKeys);
    /// </summary>
    const ACTUALIZAR_DATOS = 600; 

    /// <summary>
    /// Codigo de Error sugerido cuando se llama en try/catch al BaseDAL.ActualizarDatosKeys(string tcTabla, DataTable toCursor, params DataField[] taKeys);
    /// </summary>
    const ACTUALIZAR_VOID = 601;

    const ACTUALIZAR_INVALID_FIELD = 602;

    const CONSULTAR_DATOS = 701;

    const CONSULTAR_DATOS_CERO_REGISTROS = 702;

    //-------------CLIENTE ERRORES------------------------
    /// <summary>
        /// Usuario no existente
        /// </summary>
        const USUARIO_NO_EXISTE = 20; 

        const USUARIO_NO_ACTIVADO=19;

        /// <summary>
        /// Campo login repetido al registrar un usuario
        /// </summary>
        const LOGIN_REPETIDO = 21;

        /// <summary>
        /// Campo email repetido al registrar un usuario
        /// </summary>
        const EMAIL_REPETIDO = 22;

        /// <summary>
        /// Usuario o Email no son válidos para el método login
        /// </summary>
        const USER_OR_MAIL_NO_VALIDO = 23;

        /// <summary>
        /// Error al registrar usuario
        /// </summary>
        const ERROR_AL_REGISTRAR_USUARIO = 24;

        /// <summary>
        /// Error al enviar email
        /// </summary>
        const ERROR_AL_ENVIAR_CORREO = 25;

        /// <summary>
        /// Al cambiar password, el password antiguo ingresado no es igual al password registrado en la base de datos
        /// </summary>
        const OLDPASSWORD_NOT_EQUAL = 26;

        /// <summary>
        /// Al cambiar el password, El nuevo password no cumple oMySQL las politicas internas de password
        /// </summary>
        const PASSWORD_NOT_SECURITY_REQUIREMENTS = 27;


    //-------------CLIENTE ERRORES------------------------
}

?>