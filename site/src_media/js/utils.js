const downloadBlob = (blob, name = 'file.txt') => {
  const blobUrl = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = blobUrl;
  link.download = name;
  document.body.appendChild(link);
  link.dispatchEvent(
    new MouseEvent('click', {
      bubbles: true,
      cancelable: true,
      view: window
    })
  );

  document.body.removeChild(link);
}

const generateZip = async (elClass) => {
  const {configure, BlobReader, BlobWriter, ZipReader, ZipWriter} = await import('@zip.js/zip.js/lib/zip.js');

  configure({
    workerScriptsPath: '/js/',
  });

  // console.log(BlobReader, BlobWriter, ZipReader, ZipWriter)
  elClass.writer = new BlobWriter("application/zip");
  elClass.ZipWriter = new ZipWriter(elClass.writer);

  const addFile = async (fileName, contents) => {
    const theBlob = new Blob([contents], { type: "text/plain" });
    await elClass.ZipWriter.add(fileName, new BlobReader(theBlob));
  };

  let blobURL;
  const queue = [];
  const files = elClass.store.files;

  files['script.php'] = files['script.php'].replace('/**{{replacement}}**/', `$this->template->name = '${elClass.store.name}';`);
  Object.keys(files).map(el => queue.push(addFile(`${el}`, files[el], elClass)));
  await Promise.all(queue);
  const zipReader = new ZipReader(new BlobReader(await elClass.ZipWriter.close()));

  try {
    await zipReader.close();
    const theBlob = await elClass.writer.getData();
    elClass.ZipWriter = null;

    downloadBlob(theBlob, `Responsive_images_overrides_for_${elClass.store.name}.zip`);
  } catch (error) {
    alert(error);
  }
}

export { generateZip };
