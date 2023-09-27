const placeholders = ["あなたが書くのを待っています...", "何をお考えですか？", "言いたいことは？", "ここに書いてください", "何かありましたか？", "コメントする..."];
let get = placeholders[Math.floor(Math.random() * placeholders.length)];
document.getElementById("bbs-textarea").placeholder = get;