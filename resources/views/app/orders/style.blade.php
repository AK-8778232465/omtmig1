<style>
    .status-btn {
        font-size: .7rem !important;
    }
    .content-loaded{
        display: none;
    }
    .frame {
      position: fixed;
      top: 50%;
      left: 50%;
      width: 400px;
      height: 400px;
      margin-top: -200px;
      margin-left: -200px;
      border-radius: 2px;
      /* background: #ffffff; */
      color: #fff;
    }

    .center {
      position: absolute;
      width: 220px;
      height: 220px;
      top: 90px;
      left: 90px;
    }

    .dot-1 {
      position: absolute;
      z-index: 3;
      width: 30px;
      height: 30px;
      top: 95px;
      left: 95px;
      background: #fff;
      border-radius: 50%;
      -webkit-animation-fill-mode: both;
              animation-fill-mode: both;
      -webkit-animation: jump-jump-1 2s cubic-bezier(0.21, 0.98, 0.6, 0.99) infinite alternate;
              animation: jump-jump-1 2s cubic-bezier(0.21, 0.98, 0.6, 0.99) infinite alternate;
    }

    .dot-2 {
      position: absolute;
      z-index: 2;
      width: 60px;
      height: 60px;
      top: 80px;
      left: 80px;
      background: #fff;
      border-radius: 50%;
      -webkit-animation-fill-mode: both;
              animation-fill-mode: both;
      -webkit-animation: jump-jump-2 2s cubic-bezier(0.21, 0.98, 0.6, 0.99) infinite alternate;
              animation: jump-jump-2 2s cubic-bezier(0.21, 0.98, 0.6, 0.99) infinite alternate;
    }

    .dot-3 {
      position: absolute;
      z-index: 1;
      width: 90px;
      height: 90px;
      top: 65px;
      left: 65px;
      background: #fff;
      border-radius: 50%;
      -webkit-animation-fill-mode: both;
              animation-fill-mode: both;
      -webkit-animation: jump-jump-3 2s cubic-bezier(0.21, 0.98, 0.6, 0.99) infinite alternate;
              animation: jump-jump-3 2s cubic-bezier(0.21, 0.98, 0.6, 0.99) infinite alternate;
    }

    @-webkit-keyframes jump-jump-1 {
      0%, 70% {
        box-shadow: 2px 2px 3px 2px rgba(0, 0, 0, 0.2);
        -webkit-transform: scale(0);
                transform: scale(0);
      }
      100% {
        box-shadow: 10px 10px 15px 0 rgba(0, 0, 0, 0.3);
        -webkit-transform: scale(1);
                transform: scale(1);
      }
    }

    @keyframes jump-jump-1 {
      0%, 70% {
        box-shadow: 2px 2px 3px 2px rgba(0, 0, 0, 0.2);
        -webkit-transform: scale(0);
                transform: scale(0);
      }
      100% {
        box-shadow: 10px 10px 15px 0 rgba(0, 0, 0, 0.3);
        -webkit-transform: scale(1);
                transform: scale(1);
      }
    }
    @-webkit-keyframes jump-jump-2 {
      0%, 40% {
        box-shadow: 2px 2px 3px 2px rgba(0, 0, 0, 0.2);
        -webkit-transform: scale(0);
                transform: scale(0);
      }
      100% {
        box-shadow: 10px 10px 15px 0 rgba(0, 0, 0, 0.3);
        -webkit-transform: scale(1);
                transform: scale(1);
      }
    }
    @keyframes jump-jump-2 {
      0%, 40% {
        box-shadow: 2px 2px 3px 2px rgba(0, 0, 0, 0.2);
        -webkit-transform: scale(0);
                transform: scale(0);
      }
      100% {
        box-shadow: 10px 10px 15px 0 rgba(0, 0, 0, 0.3);
        -webkit-transform: scale(1);
                transform: scale(1);
      }
    }
    @-webkit-keyframes jump-jump-3 {
      0%, 10% {
        box-shadow: 2px 2px 3px 2px rgba(0, 0, 0, 0.2);
        -webkit-transform: scale(0);
                transform: scale(0);
      }
      100% {
        box-shadow: 10px 10px 15px 0 rgba(0, 0, 0, 0.3);
        -webkit-transform: scale(1);
                transform: scale(1);
      }
    }
    @keyframes jump-jump-3 {
      0%, 10% {
        box-shadow: 2px 2px 3px 2px rgba(0, 0, 0, 0.2);
        -webkit-transform: scale(0);
                transform: scale(0);
      }
      100% {
        box-shadow: 10px 10px 15px 0 rgba(0, 0, 0, 0.3);
        -webkit-transform: scale(1);
                transform: scale(1);
      }
    }

    .assign-me {
        background-color: #7680ff;
    }

    .assign-me:hover {
        background-color: #3f4bfd;
        cursor: pointer;
    }

    .goto-order {
        background-color: #7680ff;
    }

    

    .goto-order1 {
        background-color: #41B680;
    }
    .goto-order2 {
        background-color: #0000FF;
    }
    .goto-order3 {
        background-color: orange;
    }
    .goto-order4 {
        background-color: red;
    }
    .goto-order5 {
        background-color: #9ba7ca;
    }
    .goto-order6 {
        background-color: #654321;
    }
    

    .goto-order:hover {
        cursor: pointer;
    }
</style>
